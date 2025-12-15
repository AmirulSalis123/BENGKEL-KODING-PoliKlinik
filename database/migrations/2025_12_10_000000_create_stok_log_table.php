<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_obat')->constrained('obat')->cascadeOnDelete();
            $table->foreignId('id_periksa')->nullable()->constrained('periksa')->nullOnDelete();
            $table->integer('jumlah');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_log');
    }
};