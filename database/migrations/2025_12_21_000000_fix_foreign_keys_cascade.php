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
        Schema::table('transaksi', function (Blueprint $table) {
            // Drop existing foreign keys jika ada
            $table->dropForeign(['dompet_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            // Add back dengan cascade
            $table->foreign('dompet_id')->references('id')->on('dompet')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('kategori')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['dompet_id']);
            $table->dropForeign(['category_id']);
        });
    }
};
