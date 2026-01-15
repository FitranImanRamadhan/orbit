<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblTicketsTable extends Migration
{
    public function up()
    {   
        Schema::create('tbl_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique();                              // untuk keduanya                     
            $table->string('jenis_ticket', 20);                                     // untuk keduanya               
            $table->integer('item_ticket');                                         // untuk keduanya

            $table->string('kategori_klaim', 100)->nullable();                      // untuk software
            $table->string('approver_level2', 100)->nullable();                     // untuk software                          
            $table->string('approver_level3', 100)->nullable();                     // untuk software                          
            $table->string('approver_level4', 100)->nullable();// level tertinggi   // untuk software        
            $table->boolean('status_level2')->nullable()->default(null);            // untuk software            
            $table->boolean('status_level3')->nullable()->default(null);            // untuk software            
            $table->boolean('status_level4')->nullable()->default(null);            // untuk software            
            $table->timestamp('date_level2')->nullable();                           // untuk software                           
            $table->timestamp('date_level3')->nullable();                           // untuk software                           
            $table->timestamp('date_level4')->nullable();                           // untuk software                         
            $table->text('remarks2')->nullable();                                   // untuk software                                   
            $table->text('remarks3')->nullable();                                   // untuk software                                   
            $table->text('remarks4')->nullable();                                   // untuk software
                                              
            $table->text('remarks')->nullable();                                    // untuk hardware
            $table->string('approver_depthead', 100)->nullable();                   // untuk hardware                    // untuk hardware
            $table->text('jenis_pengecekan')->nullable();                           // untuk hardware
            $table->text('counter_measure')->nullable();                            // untuk hardware
            $table->text('next_plan')->nullable();                                  // untuk hardware
            $table->string('status_perbaikan', 20)->nullable();                     // untuk hardware
            $table->boolean('usercreate_confirm')->nullable()->default(null);       // untuk hardware
            $table->timestamp('date_usercreate_confirm')->nullable();               // untuk hardware
            $table->string('status_akhir_user', 20)->nullable();                    // untuk hardware
            
            $table->text('deskripsi')->nullable();                                  // untuk keduanya
            $table->string('user_create', 100);                                     // untuk keduanya
            $table->dateTime('tgl_permintaan');                                     // untuk keduanya
            $table->string('status_approval', 30)->default('waiting');              // untuk keduanya
            $table->string('it_start')->nullable();                                 // untuk keduanya
            $table->string('it_finish')->nullable();                                // untuk keduanya
            $table->timestamp('time_start')->nullable();                            // untuk keduanya
            $table->timestamp('time_finish')->nullable();                           // untuk keduanya
            $table->string('status_problem', 30)->nullable()->default(null);                  // untuk keduanya
            $table->string('jenis_problem', 30)->nullable();                        // untuk keduanya
            $table->string('file1')->nullable();                                    // untuk keduanya
            $table->string('file2')->nullable();                                    // untuk keduanya
            $table->string('file3')->nullable();                                    // untuk keduanya
            $table->string('priority', 30)->default('medium');// low, medium, high  // untuk keduanya
            
            $table->timestamps(); // created_at & updated_at

            $table->index('ticket_no', 'idx_tickets_ticket_no');
            $table->index('user_create', 'idx_tickets_user_create');
            $table->index('status_problem', 'idx_tickets_status_problem');
            $table->index('tgl_permintaan', 'idx_tickets_tgl_permintaan');
            $table->index('jenis_ticket', 'idx_tickets_jenis_ticket');
            $table->index('approver_level2', 'idx_tickets_level2');
            $table->index('approver_level3', 'idx_tickets_level3');
            $table->index('approver_level4', 'idx_tickets_level4');

        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_tickets');
    }
}

