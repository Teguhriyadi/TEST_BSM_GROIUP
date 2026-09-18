<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_saldo_awal', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('cabang_id')
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->foreignUuid('coa_id')
                ->constrained('coa')
                ->restrictOnDelete();

            $table->string('periode', 7);

            $table->decimal('saldo_awal_debet', 15, 2)->default(0);
            $table->decimal('saldo_awal_kredit', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(
                ['cabang_id', 'coa_id', 'periode'],
                'coa_saldo_awal_unique_cabang_coa_periode'
            );
            $table->index(['periode', 'cabang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_saldo_awal');
    }
};
