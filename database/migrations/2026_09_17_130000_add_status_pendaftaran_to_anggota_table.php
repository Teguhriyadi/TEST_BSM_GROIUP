<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->enum('status_pendaftaran', [
                'menunggu_verifikasi',
                'disetujui',
                'ditolak',
                'tidak_perlu_verifikasi',
            ])->default('tidak_perlu_verifikasi')
                ->after('status');

            $table->timestamp('tgl_verifikasi_pendaftaran')->nullable()->after('status_pendaftaran');
            $table->text('catatan_verifikasi_pendaftaran')->nullable()->after('tgl_verifikasi_pendaftaran');
            $table->foreignUuid('verifikator_users_id')->nullable()->after('catatan_verifikasi_pendaftaran')
                ->constrained('users')->nullOnDelete();

            $table->string('no_anggota', 30)->nullable(true)->change();
            $table->string('nik', 16)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verifikator_users_id');
            $table->dropColumn([
                'status_pendaftaran',
                'tgl_verifikasi_pendaftaran',
                'catatan_verifikasi_pendaftaran',
            ]);
            $table->string('nik', 16)->nullable(true)->change();
            $table->string('no_anggota', 30)->nullable(false)->change();
        });
    }
};
