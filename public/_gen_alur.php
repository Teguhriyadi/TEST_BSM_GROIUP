<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prompt = (
    "Profesional horizontal flowchart diagram 4 panel 2 baris 2 kolom, warna tema BIRU #0284c7 dan ORANYE #f97316, " .
    "latar putih BERSIH TANPA HIASAN APAPUN JANGAN ADA bintang sparkle bulat belah ketupat ikon dekoratif hiasan apapun. " .
    "PANEL 1 KIRI ATAS JUDUL ALUR LOGIN LUPA PASSWORD: Login Page -> Link Lupa Password -> Isi Email Terdaftar -> " .
    "Kirim Email Link Reset via SMTP Gmail -> Klik Link di Email -> Validasi Token dan Email Cocok -> " .
    "Isi Password Baru Konfirmasi Password -> force change password OTOMATIS JADI NOL -> Login Dashboard Tanpa Modal Reset. " .
    "PANEL 2 KANAN ATAS JUDUL ALUR PENGAJUAN PINJAMAN: Pilih Jenis Pinjaman -> Isi Form Pengajuan -> Simpan status diajukan -> " .
    "Auto Generate Daftar Dokumen Wajib sesuai jenis pinjaman -> Upload per dokumen JPG PNG auto compress image helper " .
    "max 1600px quality 80 PDF langsung simpan -> Status Dokumen Menunggu Verifikasi. " .
    "PANEL 3 KIRI BAWAH JUDUL VERIFIKASI DOKUMEN OLEH VALIDATOR: Buka Detail Pengajuan -> " .
    "Cek dokumen satu per satu -> Pilih Setujui ATAU Tolak wajib isi catatan ATAU Perlu Diperbaiki wajib isi catatan -> " .
    "Jika tolak atau perlu perbaiki anggota upload ulang reset status menunggu verifikasi. " .
    "PANEL 4 KANAN BAWAH JUDUL ATURAN APPROVAL PINJAMAN: Cek Semua Dokumen Wajib status Disetujui? -> TIDAK -> " .
    "Tampilkan daftar hambatan, tombol verifikasi pengajuan TIDAK MUNCUL -> YA -> Tombol Verifikasi Pengajuan AKTIF -> klik -> " .
    "Status Pinjaman berubah jadi DIVERIFIKASI -> Approval Pinjaman Setujui atau Tolak -> Jika Disetujui -> Pencairan -> " .
    "Status Berjalan -> Angsuran Bulanan -> Lunas. " .
    "Semua node pakai rectangle flat persegi panjang biasa tanpa sudut terlalu bulat, panah hitam tegas, " .
    "tulisan BAHASA INDONESIA jelas. TIDAK BOLEH ADA ELEMEN DEKORATIF SELAIN KOTAK DAN PANAH FLOWCHART SAJA. " .
    "Layout 2 baris 2 kolom rapi, setiap panel dipisah dengan border kotak tipis."
);

$url = "https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=" . urlencode($prompt) . "&image_size=landscape_16_9";
$targetRoot   = base_path('alur.png');
$targetPublic = public_path('alur.png');

echo "Mengunduh diagram alur...<br>\n";
echo "URL sumber: <a href='" . htmlspecialchars($url) . "' target='_blank'>Buka langsung gambar asli di tab baru</a><br><br>\n";

$img = file_get_contents($url);
if ($img === false || strlen($img) < 5000) {
    http_response_code(500);
    echo "Gagal mengunduh dari API. Solusi: klik link 'Buka langsung gambar asli' di atas, lalu tekan CMD+S / CTRL+S, simpan sebagai <b>alur.png</b> di folder root project (sejajar dengan file .env).";
    exit;
}

file_put_contents($targetRoot,   $img);
file_put_contents($targetPublic, $img);

echo "✅ SELESAI. 2 file alur.png tersimpan:<br>\n";
echo "&nbsp;&nbsp;1. Root project (sejajar .env) → <code>" . htmlspecialchars($targetRoot)   . "</code> (" . number_format(strlen($img) / 1024, 1) . " KB)<br>\n";
echo "&nbsp;&nbsp;2. Public folder (untuk preview web) → <code>" . htmlspecialchars($targetPublic) . "</code><br><br>\n";
echo "<b style='color:#dc2626'>SETELAH MEMASTIKAN alur.png ADA DI ROOT PROJECT (sejajar .env), SEGERA HAPUS FILE <code>public/_gen_alur.php</code> agar tidak bisa diakses publik.</b><br><br>\n";
echo "Preview:<br><img src='/alur.png' style='max-width:100%;border:1px solid #e5e7eb;border-radius:6px;'>\n";
