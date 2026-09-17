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
        Schema::create('angsuran', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('pinjaman_id')
                ->constrained('pinjaman')
                ->cascadeOnDelete();

            $table->integer('angsuran_ke');

            $table->date('tanggal_jatuh_tempo');

            $table->decimal('nominal', 15, 2);
            $table->decimal('denda', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);

            $table->enum('status', [
                'belum_lunas',
                'lunas',
                'telat'
            ])->default('belum_lunas');

            $table->timestamps();

            $table->unique(['pinjaman_id', 'angsuran_ke']);
            $table->index(['pinjaman_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angsuran');
    }
};
