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
        Schema::create('anggota', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('cabang_id')
                ->constrained('cabang')
                ->restrictOnDelete();

            $table->foreignUuid('users_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('no_anggota', 30)->unique();
            $table->string('nik', 16)->nullable();
            $table->string('nama', 100);

            $table->enum('jenis_kelamin', ['L', 'P']);

            $table->text('alamat')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('no_hp', 15)->nullable();

            $table->date('status_anggota')->nullable();

            $table->enum('status', [
                'aktif',
                'nonaktif'
            ])->default('aktif');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
