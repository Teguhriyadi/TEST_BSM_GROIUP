<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Anggota Berhasil Diajukan</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; padding: 2rem; color: #1e293b; }
        .wrap { max-width: 620px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.25rem 2.5rem; }
        h1 { color: #0284c7; font-size: 1.6rem; margin-top: 0; }
        .info { background: #f0f9ff; border-left: 4px solid #0284c7; padding: 0.9rem 1rem; border-radius: 6px; margin: 1.25rem 0; color: #075985; line-height: 1.55; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
        th, td { padding: 0.55rem 0.7rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { width: 40%; color: #64748b; font-weight: 600; }
        .footer { color: #64748b; font-size: 0.82rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
        .brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        .brand-badge { width: 48px; height: 48px; background: linear-gradient(135deg, #0284c7, #0369a1); color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 800; font-size: 1.1rem; }
        .brand-text h2 { margin: 0; font-size: 1.15rem; color: #0f172a; }
        .brand-text p { margin: 0; color: #64748b; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">
            <div class="brand-text">
                <h2>Koperasi Simpan Pinjam BSM</h2>
                <p>Notifikasi Otomatis Sistem</p>
            </div>
        </div>

        <h1>Terima Kasih, Pendaftaran Anda Telah Kami Terima</h1>

        <p>Yth. <b>{{ $anggota->nama }}</b>,</p>
        <p>Kami telah menerima pendaftaran Anda sebagai calon anggota Koperasi Simpan Pinjam BSM. Berikut adalah detail pendaftaran Anda:</p>

        <div class="info">
            Saat ini status pendaftaran Anda adalah <b>Menunggu Verifikasi</b>. Petugas cabang kami akan memeriksa kelengkapan & kevalidan data Anda dalam 1x24 jam kerja. Anda akan menerima email kembali dari kami berisi hasil keputusan (disetujui / ditolak) beserta langkah selanjutnya.
        </div>

        <table>
            <tr><th>Waktu Pendaftaran</th><td>{{ $anggota->created_at ? \Illuminate\Support\Carbon::parse($anggota->created_at)->translatedFormat('l, d F Y H:i') : '-' }} WIB</td></tr>
            <tr><th>Cabang Pendaftaran</th><td>{{ $anggota->cabang?->nama_cabang ?? '-' }}</td></tr>
            <tr><th>NIK</th><td>{{ $anggota->nik }}</td></tr>
            <tr><th>Nama Lengkap</th><td>{{ $anggota->nama }}</td></tr>
            <tr><th>Jenis Kelamin</th><td>{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
            <tr><th>Tanggal Lahir</th><td>{{ $anggota->tgl_lahir ? \Illuminate\Support\Carbon::parse($anggota->tgl_lahir)->translatedFormat('d F Y') : '-' }}</td></tr>
            <tr><th>No. Handphone</th><td>{{ $anggota->no_hp ?? '-' }}</td></tr>
            <tr><th>Email Aktif</th><td>{{ $anggota->user?->email ?? '-' }}</td></tr>
            <tr><th>Alamat</th><td>{{ $anggota->alamat ?? '-' }}</td></tr>
        </table>

        <div class="info">
            <b>Jika disetujui:</b> Anda akan mendapatkan Nomor Anggota resmi serta kredensial login berupa email + kata sandi default untuk mengakses portal anggota.
            <br><b>Jika terdapat kekurangan:</b> Petugas kami akan menghubungi Anda via nomor handphone terdaftar atau meminta pelengkapan melalui email ini.
        </div>

        <p>Jika ada pertanyaan lebih lanjut, silakan hubungi Kantor Cabang {{ $anggota->cabang?->nama_cabang ?? 'terdekat' }} pada jam kerja operasional.</p>

        <div class="footer">
            Ini adalah email otomatis yang dikirim oleh sistem. Mohon jangan membalas email ini. Salam hangat,<br>
            <b>Administrasi Koperasi Simpan Pinjam BSM</b>
        </div>
    </div>
</body>
</html>
