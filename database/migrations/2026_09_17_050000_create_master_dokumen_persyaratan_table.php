<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_dokumen', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kode_dokumen', 50)->unique();
            $table->string('nama_dokumen', 150);
            $table->text('deskripsi')->nullable();

            $table->string('format_diperbolehkan', 200)
                ->default('jpg,jpeg,png,pdf')
                ->comment('Daftar ekstensi dipisah koma');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_dokumen');
    }
};
