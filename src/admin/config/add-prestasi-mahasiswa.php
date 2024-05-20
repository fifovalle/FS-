<?php
include 'databases.php';
ob_start();

if (isset($_POST['Simpan'])) {
    $gambar = '';
    $pesanKesalahan = '';

    if (isset($_FILES['Gambar']) && $_FILES['Gambar']['error'] === UPLOAD_ERR_OK) {
        $gambarFile = $_FILES['Gambar'];
        $namaGambar = mysqli_real_escape_string($koneksi, htmlspecialchars($gambarFile['name']));
        $gambarTemp = $gambarFile['tmp_name'];
        $ukuranGambar = $gambarFile['size'];
        $errorGambar = $gambarFile['error'];

        $ukuranMaksimal = 2 * 1024 * 1024; // 2MB
        $apakahUnggahBerhasil = ($errorGambar === 0 && !empty($namaGambar)) && ($ukuranGambar <= $ukuranMaksimal);
        $pesanKesalahan .= (!$apakahUnggahBerhasil && empty($pesanKesalahan)) ? "Gagal mengupload gambar atau gambar tidak diunggah atau ukuran melebihi batas maksimal 2MB." : '';

        $formatYangDisetujui = ['jpg', 'jpeg', 'png'];
        $formatGambar = strtolower(pathinfo($namaGambar, PATHINFO_EXTENSION));
        $apakahFormatDisetujui = in_array($formatGambar, $formatYangDisetujui);
        $pesanKesalahan .= (!$apakahFormatDisetujui && empty($pesanKesalahan)) ? "Format gambar harus berupa JPG, JPEG, atau PNG." : '';

        $namaGambarBaru = $apakahFormatDisetujui ? uniqid() . '.' . $formatGambar : '';
        $tujuanGambar = $apakahFormatDisetujui ? '../../uploads/' . $namaGambarBaru : '';

        if ($apakahUnggahBerhasil && $apakahFormatDisetujui && move_uploaded_file($gambarTemp, $tujuanGambar)) {
            $gambar = $namaGambarBaru;
        } else {
            setPesanKesalahan($pesanKesalahan);
            header("Location: $akar_tautan" . "src/admin/pages/prestasi-mahasiswa.php");
            exit;
        }
    }

    $namaMahasiswa = mysqli_real_escape_string($koneksi, $_POST['Nama_Mahasiswa']);
    $kegiatan = mysqli_real_escape_string($koneksi, $_POST['Kegiatan']);
    $pencapaian = mysqli_real_escape_string($koneksi, $_POST['Pencapaian']);
    $tahunPencapaian = mysqli_real_escape_string($koneksi, $_POST['Tahun_Pencapaian']);

    $objekPrestasiMahasiswa = new prestasiMahasiswa($koneksi);

    $dataPrestasiMahasiswa = array(
        "ID_Admin" => $_SESSION['ID_Admin'],
        "Gambar" => $gambar,
        'Nama_Mahasiswa' => $namaMahasiswa,
        'Kegiatan' => $kegiatan,
        'Pencapaian' => $pencapaian,
        'Tahun_Pencapaian' => $tahunPencapaian,
    );

    $simpanDataPrestasiMahasiswa = $objekPrestasiMahasiswa->tambahPrestasiMahasiswa($dataPrestasiMahasiswa);

    if ($simpanDataPrestasiMahasiswa) {
        setPesanKeberhasilan("Berhasil menyimpan data Prestasi Mahasiswa.");
    } else {
        setPesanKesalahan("Gagal menyimpan data Prestasi Mahasiswa.");
    }
    header("Location: $akar_tautan" . "src/admin/pages/prestasi-mahasiswa.php");
    exit;
} else {
    header("Location: $akar_tautan" . "src/admin/pages/prestasi-mahasiswa.php");
    exit;
}
ob_end_flush();
