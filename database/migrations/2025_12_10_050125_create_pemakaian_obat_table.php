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
    Schema::create('pemakaian_obat', function (Blueprint $table) {
        $table->id();
        $table->foreignId('obat_id')->constrained('obat')->onDelete('cascade');
        $table->integer('jumlah')->default(0);
        $table->string('nama_dokter')->nullable();
        $table->text('keterangan')->nullable();
        $table->timestamp('tanggal_pemakaian')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian_obat');
    }
};
