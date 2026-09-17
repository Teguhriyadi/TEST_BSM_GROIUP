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
        Schema::create('jenis_simpanan', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->string('nama_jenis', 100);
            $table->text('keterangan')->nullable();

            $table->decimal('setoran_minimal', 15, 2)->default(0);
            $table->decimal('setoran_maksimal', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_simpanan');
    }
};
