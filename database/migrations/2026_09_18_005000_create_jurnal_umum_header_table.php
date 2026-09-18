<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_umum_header', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('cabang_id')
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->foreignUuid('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignUuid('diposting_oleh')->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nomor_jurnal', 50)->unique();

            $table->enum('tipe', ['manual', 'otomatis'])->default('manual');
            $table->enum('status', ['draf', 'diposting', 'dibatalkan'])->default('draf');

            $table->date('tanggal_jurnal');
            $table->string('ref_id', 50)->nullable();
            $table->string('ref_tipe', 100)->nullable();

            $table->text('keterangan')->nullable();

            $table->decimal('total_debet', 15, 2)->default(0);
            $table->decimal('total_kredit', 15, 2)->default(0);

            $table->timestamp('diposting_at')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'tanggal_jurnal', 'status']);
            $table->index(['ref_id', 'ref_tipe']);
            $table->index(['status', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum_header');
    }
};
