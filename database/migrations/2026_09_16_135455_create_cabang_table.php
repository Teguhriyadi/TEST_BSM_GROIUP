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
        Schema::create('cabang', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("kode_cabang", 50)->unique();
            $table->string("nama_cabang", 100);
            $table->string("alamat", 255);
            $table->string("telepon", 30);
            $table->enum("is_active", ["1", "0"])->default("1");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabang');
    }
};
