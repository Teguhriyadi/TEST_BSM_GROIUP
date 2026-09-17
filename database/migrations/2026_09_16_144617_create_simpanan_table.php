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
        Schema::create('simpanan', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('anggota_id')
                ->constrained('anggota')
                ->restrictOnDelete();

            $table->foreignUuid('cabang_id')
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->foreignUuid('jenis_simpanan_id')
                ->constrained('jenis_simpanan')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo', 15, 2)->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['anggota_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simpanan');
    }
};
