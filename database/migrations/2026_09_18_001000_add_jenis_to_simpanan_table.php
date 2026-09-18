<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simpanan', function (Blueprint $table) {
            if (!Schema::hasColumn('simpanan', 'jenis')) {
                $table->enum('jenis', ['setoran', 'penarikan'])
                    ->default('setoran')
                    ->after('jenis_simpanan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('simpanan', function (Blueprint $table) {
            if (Schema::hasColumn('simpanan', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }
};
