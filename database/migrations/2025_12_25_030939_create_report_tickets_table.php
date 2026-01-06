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
            $table->string('user_create', 100);     
            $table->string('approver_level2', 100)->nullable();                                             
            $table->string('approver_level3', 100)->nullable();                                                  
            $table->boolean('status_level2')->nullable()->default(null);                      
            $table->boolean('status_level3')->nullable()->default(null);                      
            $table->timestamp('date_level2')->nullable();                                                    
            $table->timestamp('date_level3')->nullable();                                                    

            $table->string('status_ticket', 30)->default('waiting'); ;

            $table->timestamps();

            // index untuk report
            $table->index(['year', 'month', 'week']);
            $table->index(['jenis_ticket']);
        });
    }

    
}
