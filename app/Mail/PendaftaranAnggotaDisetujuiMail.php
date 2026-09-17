<?php

namespace App\Mail;

use App\Models\Anggota;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendaftaranAnggotaDisetujuiMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Anggota $anggota,
        public string $passwordDefault,
        public string $emailLogin
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat! Pendaftaran Anggota Koperasi Anda Disetujui',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pendaftaran-anggota-disetujui',
            with: [
                'anggota' => $this->anggota,
                'passwordDefault' => $this->passwordDefault,
                'emailLogin' => $this->emailLogin,
            ]
        );
    }
}
