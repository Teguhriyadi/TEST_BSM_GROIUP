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
        Schema::create('pembayaran_angsuran', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('angsuran_id')
                ->constrained('angsuran')
                ->restrictOnDelete();

            $table->date('tanggal_bayar');

            $table->decimal('jumlah_bayar', 15, 2);

            $table->enum('metode_pembayaran', [
                'tunai',
                'transfer',
                'lainnya'
            ])->default('tunai');

            $table->string('bukti_pembayaran', 255)->nullable();

            $table->foreignUuid('dibayar_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['angsuran_id', 'tanggal_bayar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_angsuran');
    }
};
