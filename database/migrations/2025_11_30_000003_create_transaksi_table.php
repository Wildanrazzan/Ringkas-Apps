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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dompet_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->decimal('amount', 15, 2);
            $table->date('trx_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('dompet_id')->references('id')->on('dompet')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('kategori')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
