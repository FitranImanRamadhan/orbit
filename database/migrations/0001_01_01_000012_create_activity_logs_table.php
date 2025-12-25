<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();   // ganti user_id jadi user_id
            $table->string('action');                 // create, update, delete, login, dll
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Optional: foreign key ke tabel users
            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};

