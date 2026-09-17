<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('anggota_id')
                ->constrained('anggota')
                ->restrictOnDelete();

            $table->foreignUuid('cabang_id')
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->foreignUuid('jenis_pinjaman_id')
                ->constrained('jenis_pinjaman')
                ->restrictOnDelete();

            $table->string('nomor_pinjaman', 30)->unique();

            $table->decimal('jumlah_pinjaman', 15, 2);
            $table->integer('tenor');

            $table->decimal('bunga', 5, 2)->default(0);
            $table->decimal('angsuran_per_bulan', 15, 2)->default(0);

            $table->text('tujuan')->nullable();

            $table->enum('status', [
                'diajukan',
                'diverifikasi',
                'disetujui',
                'ditolak',
                'dicairkan',
                'berjalan',
                'lunas',
                'dibatalkan'
            ])->default('diajukan');

            $table->date('tgl_pengajuan')->nullable();
            $table->date('tgl_cair')->nullable();

            $table->timestamps();

            $table->index(['anggota_id', 'status']);
            $table->index(['cabang_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
