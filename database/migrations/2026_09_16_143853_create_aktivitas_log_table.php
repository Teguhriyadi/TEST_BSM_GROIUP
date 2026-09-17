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
        Schema::create('aktivitas_log', function (Blueprint $table) {
            $table->uuid("id")->primary();

            $table->foreignUuid('users_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('cabang_id')
                ->nullable()
                ->constrained('cabang')
                ->nullOnDelete();

            $table->string('tipe_aktivitas', 50);

            $table->string('model_type', 100)->nullable();
            $table->string('model_id', 100)->nullable();

            $table->text('deskripsi')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('users_agent')->nullable();

            $table->timestamps();

            $table->index(['users_id', 'created_at']);
            $table->index(['cabang_id', 'created_at']);
            $table->index('tipe_aktivitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_log');
    }
};
