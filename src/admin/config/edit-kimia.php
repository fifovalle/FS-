<?php
include 'databases.php';
ob_start();

function containsXSS($input)
{
    $xss_patterns = [
        "/<script\b[^>]*>(.*?)<\/script>/is",
        "/<img\b[^>]*src[\s]*=[\s]*[\"]*javascript:/i",
        "/<iframe\b[^>]*>(.*?)<\/iframe>/is",
        "/<link\b[^>]*href[\s]*=[\s]*[\"]*javascript:/i",
        "/<object\b[^>]*>(.*?)<\/object>/is",
        "/on[a-zA-Z]+\s*=\s*\"[^\"]*\"/i",
        "/on[a-zA-Z]+\s*=\s*\"[^\"]*\"/i",
        "/<script\b[^>]*>[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i",
        "/<a\b[^>]*href\s*=\s*(?:\"|')(?:javascript:|.*?\"javascript:).*?(?:\"|')/i",
        "/<embed\b[^>]*>(.*?)<\/embed>/is",
        "/<applet\b[^>]*>(.*?)<\/applet>/is",
        "/<!--.*?-->/",
        "/(<script\b[^>]*>(.*?)<\/script>|<img\b[^>]*src[\s]*=[\s]*[\"]*javascript:|<iframe\b[^>]*>(.*?)<\/iframe>|<link\b[^>]*href[\s]*=[\s]*[\"]*javascript:|<object\b[^>]*>(.*?)<\/object>|on[a-zA-Z]+\s*=\s*\"[^\"]*\"|<[^>]*(>|$)(?:<|>)+|<[^>]*script\s*.*?(?:>|$)|<![^>]*-->|eval\s*\((.*?)\)|setTimeout\s*\((.*?)\)|<[^>]*\bstyle\s*=\s*[\"'][^\"']*[;{][^\"']*['\"]|<meta[^>]*http-equiv=[\"']?refresh[\"']?[^>]*url=|<[^>]*src\s*=\s*\"[^>]*\"[^>]*>|expression\s*\((.*?)\))/i"
    ];

    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $idPenelitianKimia = filter_input(INPUT_POST, 'ID_Penelitian_Kimia', FILTER_SANITIZE_STRING);
    $judulPenelitian = filter_input(INPUT_POST, 'Judul_Penelitian', FILTER_SANITIZE_STRING);
    $tautanPenelitian = filter_input(INPUT_POST, 'Link_Penelitian', FILTER_SANITIZE_URL);
    $tingkatan = filter_input(INPUT_POST, 'Tingkatan', FILTER_SANITIZE_STRING);
    $judulJurnal = filter_input(INPUT_POST, 'Judul_Journal', FILTER_SANITIZE_STRING);
    $tautanJurnal = filter_input(INPUT_POST, 'Link_Journal', FILTER_SANITIZE_URL);
    $creator = filter_input(INPUT_POST, 'Creator', FILTER_SANITIZE_STRING);
    $tahunJurnal = filter_input(INPUT_POST, 'Tahun', FILTER_SANITIZE_STRING);


    $penelitianKimia = new Kimia($koneksi);

    if (!is_numeric($idPenelitianKimia) || $idPenelitianKimia <= 0) {
        echo json_encode(array("success" => false, "message" => "ID kimia tidak valid."));
        exit;
    }

    $pattern = "/^https:\/\/.+$/";

    if (!preg_match($pattern, $tautanPenelitian)) {
        echo json_encode(array("success" => false, "message" => "Tautan Penelitian tidak valid. Harus menggunakan format https."));
        exit;
    }

    if (!preg_match($pattern, $tautanJurnal)) {
        echo json_encode(array("success" => false, "message" => "Tautan Jurnal tidak valid. Harus menggunakan format https."));
        exit;
    }

    $dataPenelitianKimia = array(
        'Judul_Penelitian' => $judulPenelitian,
        'Tautan_Penelitian' => $tautanPenelitian,
        'Tingkatan' => $tingkatan,
        'Judul_Jurnal' => $judulJurnal,
        'Tautan_Jurnal' => $tautanJurnal,
        'Pencipta' => $creator,
        'Tahun' => $tahunJurnal
    );

    $updateDataPenelitianKimia = $penelitianKimia->perbaruiKimia($idPenelitianKimia, $dataPenelitianKimia);

    if ($updateDataPenelitianKimia) {
        echo json_encode(array("success" => true, "message" => "Data kimia berhasil diperbarui."));
        exit;
    } else {
        echo json_encode(array("success" => false, "message" => "Gagal memperbarui data kimia."));
        exit;
    }
} else {
    echo json_encode(array("success" => false, "message" => "Metode request tidak valid."));
    exit;
}
ob_end_flush();
