<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjaman_dokumen', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('pinjaman_id')
                ->constrained('pinjaman')
                ->cascadeOnDelete();

            $table->foreignUuid('master_dokumen_id')
                ->constrained('master_dokumen')
                ->restrictOnDelete();

            $table->foreignUuid('uploader_users_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('file_path', 500)->nullable();
            $table->string('nama_file_asli', 255)->nullable();
            $table->unsignedBigInteger('ukuran_file')->nullable()->comment('Dalam byte');
            $table->string('tipe_mime', 100)->nullable();

            $table->enum('status', [
                'belum_diunggah',
                'menunggu_verifikasi',
                'disetujui',
                'ditolak',
                'perlu_diperbaiki',
            ])->default('belum_diunggah');

            $table->text('catatan')->nullable();

            $table->foreignUuid('verifikator_users_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('tgl_verifikasi')->nullable();

            $table->timestamps();

            $table->unique(['pinjaman_id', 'master_dokumen_id'], 'pd_pj_id_md_id_unique');
            $table->index(['pinjaman_id', 'status'], 'pd_pj_id_status_index');
            $table->index('status', 'pd_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman_dokumen');
    }
};
