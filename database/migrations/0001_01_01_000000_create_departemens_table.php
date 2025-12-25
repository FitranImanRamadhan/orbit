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
        Schema::create('departemens', function (Blueprint $table) {
            $table->id('id_departemen'); // primary key
            $table->string('nama_departemen', 60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.WI
     */
    public function down(): void
    {
        Schema::dropIfExists('departemens');
    }
};
