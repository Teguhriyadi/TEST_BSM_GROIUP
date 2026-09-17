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
        Schema::create('jenis_pinjaman', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->string('nama_jenis', 100);
            $table->text('keterangan')->nullable();

            $table->decimal('maksimal_plafon', 15, 2)->nullable();
            $table->decimal('bunga_tahunan', 5, 2)->default(0);

            $table->decimal('tenor_minimal', 15, 2)->nullable();
            $table->integer('tenor_maksimal')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pinjaman');
    }
};
