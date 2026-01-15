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
            $table->string('type', 60)->nullable();
            $table->string('username'); 
            $table->unsignedBigInteger('plant_id')->nullable(); // penting untuk membedakan user
            $table->text('message');
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->timestamps();

            $table->index('username', 'idx_notif_username');
            $table->index('plant_id', 'idx_notif_plant_id');
            $table->index('status', 'idx_notif_status');
            $table->index('ticket_no', 'idx_notif_ticket_no');
            $table->index(['username', 'plant_id'], 'idx_notif_user_plant');
            $table->index(['username', 'plant_id', 'status'], 'idx_notif_unread');

        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
