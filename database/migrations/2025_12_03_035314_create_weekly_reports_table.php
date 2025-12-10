<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('year'); // Tahun (2025)
            $table->integer('month'); // Bulan (1-12)
            $table->integer('week'); // Minggu ke- dalam bulan (1-5)
            $table->date('start_date'); // Tanggal mulai minggu
            $table->date('end_date'); // Tanggal akhir minggu
            $table->integer('stok_awal'); // Stok awal minggu
            $table->integer('total_penambahan')->default(0); // Total penambahan dalam minggu
            $table->integer('total_pengurangan')->default(0); // Total pengurangan dalam minggu
            $table->integer('stok_akhir'); // Stok akhir minggu
            $table->timestamps();

            // Index untuk query cepat
            $table->index(['item_id', 'year', 'month', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
