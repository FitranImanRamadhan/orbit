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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user'); // sudah auto increment + primary key
            $table->string('nik', 100); // ← ini penting!
            $table->string('nama_lengkap', 100);
            $table->unsignedBigInteger('plant_id')->nullable();
            $table->unsignedBigInteger('departemen_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->string('user_akses', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->string('username', 100)->unique();
            $table->string('password', 100);

            $table->foreign('plant_id')->references('id_plant')->on('plants')->onDelete('set null');
            $table->foreign('departemen_id')->references('id_departemen')->on('departemens')->onDelete('set null');
            $table->foreign('position_id')->references('id_position')->on('positions')->onDelete('set null');

            $table->rememberToken();
            $table->timestamps();

            $table->index('plant_id', 'idx_users_plant');
            $table->index('departemen_id', 'idx_users_departemen');
            $table->index('position_id', 'idx_users_position');
            $table->index('user_akses', 'idx_users_user_akses');
            $table->index(['plant_id', 'departemen_id'], 'idx_users_plant_departemen_position');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // <-- ubah di sini
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('set null');
        });

    

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
