<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_angsuran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran_angsuran', 'cabang_id')) {
                $table->foreignUuid('cabang_id')->nullable()
                    ->after('id')
                    ->constrained('cabang')
                    ->restrictOnDelete();
            }
            if (!Schema::hasColumn('pembayaran_angsuran', 'jumlah_pokok')) {
                $table->decimal('jumlah_pokok', 15, 2)->default(0)->after('jumlah_bayar');
            }
            if (!Schema::hasColumn('pembayaran_angsuran', 'jumlah_bunga')) {
                $table->decimal('jumlah_bunga', 15, 2)->default(0)->after('jumlah_pokok');
            }
            if (!Schema::hasColumn('pembayaran_angsuran', 'jumlah_denda')) {
                $table->decimal('jumlah_denda', 15, 2)->default(0)->after('jumlah_bunga');
            }
            if (!Schema::hasColumn('pembayaran_angsuran', 'biaya_administrasi')) {
                $table->decimal('biaya_administrasi', 15, 2)->default(0)->after('jumlah_denda');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_angsuran', function (Blueprint $table) {
            $cols = ['biaya_administrasi', 'jumlah_denda', 'jumlah_bunga', 'jumlah_pokok'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('pembayaran_angsuran', $c)) {
                    $table->dropColumn($c);
                }
            }
            if (Schema::hasColumn('pembayaran_angsuran', 'cabang_id')) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            }
        });
    }
};
