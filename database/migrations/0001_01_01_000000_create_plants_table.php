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
        Schema::create('plants', function (Blueprint $table) {
            $table->id('id_plant'); // primary key
            $table->string('nama_plant', 60);
            $table->string('label', 3)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.WI
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
