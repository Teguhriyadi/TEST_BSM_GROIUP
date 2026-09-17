<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Anggota Ditolak</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fef2f2; padding: 2rem; color: #1e293b; }
        .wrap { max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #fecaca; border-radius: 12px; padding: 2.25rem 2.5rem; }
        h1 { color: #b91c1c; font-size: 1.65rem; margin-top: 0.5rem; }
        .reject { background: #fee2e2; border-left: 4px solid #ef4444; padding: 0.9rem 1rem; border-radius: 6px; margin: 1.25rem 0; color: #991b1b; line-height: 1.6; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
        th, td { padding: 0.55rem 0.7rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { width: 40%; color: #64748b; font-weight: 600; }
        .footer { color: #64748b; font-size: 0.82rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
        .brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
        .brand-badge { width: 48px; height: 48px; background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 800; font-size: 1.2rem; }
        .brand-text h2 { margin: 0; font-size: 1.15rem; color: #0f172a; }
        .brand-text p { margin: 0; color: #64748b; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">
            <div class="brand-text">
                <h2>Koperasi Simpan Pinjam BSM</h2>
                <p>Hasil Verifikasi Pendaftaran Anggota</p>
            </div>
        </div>

        <h1>Pendaftaran Anda Belum Dapat Diterima</h1>

        <p>Yth. <b>{{ $anggota->nama }}</b>,</p>
        <p>Kami sampaikan bahwa berdasarkan hasil verifikasi pada tanggal <b>{{ $anggota->tgl_verifikasi_pendaftaran ? \Illuminate\Support\Carbon::parse($anggota->tgl_verifikasi_pendaftaran)->translatedFormat('l, d F Y') : '-' }}</b>, pendaftaran anggota Anda untuk saat ini <b>BELUM DAPAT DITERIMA</b>.</p>

        <div class="reject">
            <b>Catatan resmi dari pihak verifikator:</b><br>
            {!! nl2br(e($anggota->catatan_verifikasi_pendaftaran ?? '-')) !!}
        </div>

        <h3 style="color:#475569;margin-top:1.5rem;">Langkah Selanjutnya</h3>
        <p>Anda tidak perlu berkecil hati. Silakan lakukan perbaikan sesuai catatan di atas, kemudian ajukan pendaftaran ulang melalui halaman pendaftaran anggota, atau kunjungi langsung Kantor Cabang <b>{{ $anggota->cabang?->nama_cabang ?? 'terdekat' }}</b> untuk berkonsultasi dengan petugas kami pada jam operasional.</p>

        <table>
            <tr><th>Nomor Pendaftaran</th><td>{{ substr($anggota->id ?? '', 0, 8) }}...</td></tr>
            <tr><th>NIK</th><td>{{ $anggota->nik }}</td></tr>
            <tr><th>Nama</th><td>{{ $anggota->nama }}</td></tr>
            <tr><th>Cabang</th><td>{{ $anggota->cabang?->nama_cabang ?? '-' }}</td></tr>
        </table>

        <div class="footer">
            Email otomatis — mohon tidak membalas. Jika ingin klarifikasi, silakan menghubungi petugas cabang pada jam kerja.<br>
            Hormat kami, <b>Administrasi Koperasi Simpan Pinjam BSM</b>.
        </div>
    </div>
</body>
</html>
