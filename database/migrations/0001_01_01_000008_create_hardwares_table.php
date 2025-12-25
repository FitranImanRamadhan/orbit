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
        Schema::create('hardwares', function (Blueprint $table) {
            $table->id('id_hardware'); // PK
            $table->string('nama_hardware', 100); // contoh: Mouse, Keyboard, Laptop
            $table->string('kategori', 50)->nullable(); // optional: input, output, peripheral
            $table->text('keterangan')->nullable();     // optional: deskripsi item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardwares');
    }
};
