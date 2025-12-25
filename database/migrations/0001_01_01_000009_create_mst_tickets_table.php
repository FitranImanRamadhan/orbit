<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_tickets', function (Blueprint $table) {
            $table->id('id_ticket');              
            $table->string('nama_ticket', 100);   
            $table->string('tipe', 100); 
            $table->string('user_create', 50);    
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_tickets');
    }
};
