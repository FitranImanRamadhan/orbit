<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('ticket_no', 50);
            $table->string('sender', 50);
            $table->string('receiver', 50)->nullable();

            $table->text('message')->nullable();
            $table->string('file_path')->nullable();

            $table->boolean('is_read')->default(false);

            $table->timestamps();

            // Index untuk percepat query
            $table->index('ticket_no');
            $table->index('sender');
            $table->index('receiver');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

