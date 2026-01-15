<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHirarkisTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('user_hirarkis'); // hapus tabel lama jika ada

        Schema::create('user_hirarkis', function (Blueprint $table) {
            $table->id('id_hirarki');
            $table->unsignedBigInteger('plant_id')->nullable();
            $table->foreign('plant_id')->references('id_plant')->on('plants')->onDelete('set null');
            $table->unsignedBigInteger('departemen_id')->nullable();
            $table->foreign('departemen_id')->references('id_departemen')->on('departemens')->onDelete('set null');
            $table->text('level5')->nullable();
            $table->text('level4')->nullable();
            $table->text('level3')->nullable();
            $table->text('level2')->nullable();
            $table->text('level1')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_hirarkis');
    }
}
