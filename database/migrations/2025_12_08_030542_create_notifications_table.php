<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
       Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no');
            $table->string('username'); 
            $table->unsignedBigInteger('plant_id'); // penting untuk membedakan user
            $table->text('message');
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->timestamps();

            // optional, kalau ingin relasi:
            // $table->foreign('username')->references('username')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
