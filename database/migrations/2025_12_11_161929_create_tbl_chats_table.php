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
        Schema::create('tbl_chats', function (Blueprint $table) {
            $table->bigIncrements('id');                       // Primary key
            $table->string('no_ticket', 50);                  // Nomor ticket
            $table->string('pengirim_username', 50);         // Username pengirim chat
            $table->string('penerima_username', 50)->nullable(); // Username penerima chat, nullable untuk grup
            $table->text('pesan');                            // Isi pesan chat
            $table->boolean('status_dibaca')->default(false); // Status penerima membaca
            $table->boolean('status_dikirim')->default(true);// Status chat terkirim
            $table->timestamps();                             // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_chats');
    }
};
