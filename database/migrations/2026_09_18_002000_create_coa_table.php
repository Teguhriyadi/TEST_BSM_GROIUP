<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('parent_id')->nullable()
                ->constrained('coa')
                ->restrictOnDelete();

            $table->foreignUuid('cabang_id')->nullable()
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->string('kode_akun', 50);
            $table->string('nama_akun', 150);
            $table->integer('level')->default(1);

            $table->enum('kelompok', [
                'aset',
                'kewajiban',
                'ekuitas',
                'pendapatan',
                'beban',
                'ikhtisar_laba_rugi'
            ]);

            $table->enum('posisi_laporan', [
                'neraca',
                'laba_rugi'
            ]);

            $table->enum('saldo_normal', ['debet', 'kredit']);

            $table->enum('is_active', ['1', '0'])->default('1');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['kode_akun', 'cabang_id'], 'coa_kode_cabang_unique');
            $table->index(['kelompok', 'level']);
            $table->index(['posisi_laporan', 'kode_akun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
