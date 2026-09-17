<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Anggota Disetujui</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0fdf4; padding: 2rem; color: #1e293b; }
        .wrap { max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #bbf7d0; border-radius: 12px; padding: 2.25rem 2.5rem; }
        h1 { color: #15803d; font-size: 1.7rem; margin-top: 0.5rem; }
        .success { background: #dcfce7; border-left: 4px solid #22c55e; padding: 0.9rem 1rem; border-radius: 6px; margin: 1.25rem 0; color: #166534; line-height: 1.55; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
        th, td { padding: 0.55rem 0.7rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { width: 42%; color: #64748b; font-weight: 600; }
        .box-creds { background: linear-gradient(135deg, #eff6ff, #ecfeff); border: 1px dashed #0284c7; border-radius: 8px; padding: 1.1rem 1.25rem; margin: 1.25rem 0; }
        .box-creds .label { font-size: 0.8rem; color: #075985; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .box-creds .value { font-family: ui-monospace, 'SFMono-Regular', Menlo, monospace; font-weight: 700; color: #0c4a6e; font-size: 1.05rem; margin-top: 0.25rem; word-break: break-all; }
        .footer { color: #64748b; font-size: 0.82rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
        .brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
        .brand-badge { width: 48px; height: 48px; background: linear-gradient(135deg, #22c55e, #15803d); color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 800; font-size: 1.1rem; }
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

        <h1>Selamat, Pendaftaran Anda Disetujui!</h1>

        <p>Yth. <b>{{ $anggota->nama }}</b>,</p>
        <p>Berdasarkan hasil verifikasi data yang Anda ajukan, dengan ini kami menyatakan pendaftaran Anda sebagai anggota Koperasi Simpan Pinjam BSM <b class="text-success">DISETUJUI</b>.</p>

        <div class="success">
            <b>Berikut data resmi keanggotaan Anda:</b>
            <table style="margin-top:0.5rem;">
                <tr><th>Nomor Anggota</th><td><code style="background:#f0fdf4;padding:0.15rem 0.4rem;border-radius:4px;color:#166534;font-weight:700;">{{ $anggota->no_anggota }}</code></td></tr>
                <tr><th>Tanggal Pengesahan</th><td>{{ $anggota->tgl_verifikasi_pendaftaran ? \Illuminate\Support\Carbon::parse($anggota->tgl_verifikasi_pendaftaran)->translatedFormat('l, d F Y H:i') : '-' }} WIB</td></tr>
                <tr><th>Cabang Domisili</th><td>{{ $anggota->cabang?->nama_cabang ?? '-' }}</td></tr>
            </table>
        </div>

        <h3 style="color:#0369a1;margin-top:1.5rem;">Kredensial Login Portal Anggota</h3>
        <p>Gunakan data berikut untuk masuk ke portal anggota (halaman login koperasi). Untuk keamanan, Anda <b>WAJIB mengganti kata sandi</b> pada sesi login pertama.</p>
        <div class="box-creds">
            <div class="label">Alamat Email</div>
            <div class="value">{{ $emailLogin }}</div>
            <div class="label mt-3">Kata Sandi Default</div>
            <div class="value">{{ $passwordDefault }}</div>
        </div>

        <p style="margin-top:1.5rem;">Untuk memulai layanan koperasi, silakan kunjungi kantor cabang untuk membayar Simpanan Pokok & Simpanan Wajib pertama, atau hubungi teller kami untuk petunjuk selengkapnya. Selamat bergabung di keluarga besar KSP BSM!</p>

        <div class="footer">
            Email otomatis — mohon tidak membalas. Jika ada pertanyaan, hubungi Kantor Cabang {{ $anggota->cabang?->nama_cabang ?? 'terdekat' }}.<br>
            Salam hangat, <b>Administrasi Koperasi Simpan Pinjam BSM</b>.
        </div>
    </div>
</body>
</html>
