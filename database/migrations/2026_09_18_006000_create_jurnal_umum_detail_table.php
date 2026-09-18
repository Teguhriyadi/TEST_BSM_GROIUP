<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_umum_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('jurnal_umum_header_id')
                ->constrained('jurnal_umum_header')
                ->cascadeOnDelete();

            $table->foreignUuid('coa_id')
                ->constrained('coa')
                ->restrictOnDelete();

            $table->string('keterangan', 255)->nullable();

            $table->decimal('debet', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['jurnal_umum_header_id', 'coa_id']);
            $table->index(['coa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum_detail');
    }
};
