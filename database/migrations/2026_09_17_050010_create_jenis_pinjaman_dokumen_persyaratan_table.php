<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pinjaman_dokumen_persyaratan', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('jenis_pinjaman_id')
                ->constrained('jenis_pinjaman')
                ->restrictOnDelete();

            $table->foreignUuid('master_dokumen_id')
                ->constrained('master_dokumen')
                ->restrictOnDelete();

            $table->boolean('is_wajib')->default(true);
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();

            $table->unique(['jenis_pinjaman_id', 'master_dokumen_id'], 'jp_dp_jp_id_md_id_unique');
            $table->index('jenis_pinjaman_id', 'jp_dp_jp_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pinjaman_dokumen_persyaratan');
    }
};
