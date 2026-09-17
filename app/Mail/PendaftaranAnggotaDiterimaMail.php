<?php

namespace App\Mail;

use App\Models\Anggota;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendaftaranAnggotaDiterimaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Anggota $anggota
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Anggota Koperasi Berhasil Diajukan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pendaftaran-anggota-diterima',
            with: [
                'anggota' => $this->anggota,
            ]
        );
    }
}
