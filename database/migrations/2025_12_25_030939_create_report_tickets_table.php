<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('report_tickets', function (Blueprint $table) {
            $table->id();

            $table->integer('year');
            $table->integer('month');
            $table->integer('week');
            $table->string('jenis_ticket', 30);          
            $table->string('approver', 100)->nullable(); 
            $table->boolean('status_approval')->nullable()->default(null);
            $table->timestamp('date_approval')->nullable();

            $table->string('status_ticket', 30)->default('waiting'); ;

            $table->timestamps();

            // index untuk report
            $table->index(['year', 'month', 'week']);
            $table->index(['jenis_ticket', 'status_approval']);
        });
    }

    
}
