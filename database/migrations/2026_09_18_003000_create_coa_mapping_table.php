<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_mapping', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('cabang_id')->nullable()
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->enum('tipe_transaksi', [
                'simpanan_setoran',
                'simpanan_penarikan',
                'pinjaman_cair',
                'angsuran_pokok',
                'angsuran_bunga',
                'biaya_administrasi',
                'denda_tunggakan'
            ]);

            $table->enum('posisi', ['debet', 'kredit']);

            $table->foreignUuid('coa_id')
                ->constrained('coa')
                ->restrictOnDelete();

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(
                ['cabang_id', 'tipe_transaksi', 'posisi'],
                'coa_mapping_unique_cabang_tipe_posisi'
            );
            $table->index(['coa_id', 'tipe_transaksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_mapping');
    }
};
