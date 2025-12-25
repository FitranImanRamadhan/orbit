<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticketing extends Model
{
    use HasFactory;

    protected $table = 'tbl_tickets';

    protected $fillable = [
        'ticket_no',
        'jenis_ticket',
        'item_ticket',

        // software
        'kategori_klaim',
        'approver_level2',
        'approver_level3',
        'approver_level4',
        'status_level2',
        'status_level3',
        'status_level4',
        'date_level2',
        'date_level3',
        'date_level4',
        'remarks2',
        'remarks3',
        'remarks4',

        // hardware
        'approver_depthead',
        'status_deptheadapprove',
        'date_deptheadapprove',
        'remarks_depthead',
        'jenis_pengecekan',
        'counter_measure',
        'next_plan',
        'status_perbaikan',
        'usercreate_confirm',
        'date_usercreate_confirm',
        'status_akhir_user',

        // umum
        'deskripsi',
        'user_create',
        'tgl_permintaan',
        'status_approval',
        'it_start',
        'it_finish',
        'time_start',
        'time_finish',
        'status_problem',
        'jenis_problem',
        'file1',
        'file2',
        'file3',
        'priority',
    ];

    protected $casts = [
        // boolean
        'status_level2' => 'boolean',
        'status_level3' => 'boolean',
        'status_level4' => 'boolean',
        'status_deptheadapprove' => 'boolean',
        'usercreate_confirm' => 'boolean',

        // datetime
        'time_start' => 'datetime',
        'time_finish' => 'datetime',
        'tgl_permintaan' => 'datetime',
        'date_level2' => 'datetime',
        'date_level3' => 'datetime',
        'date_level4' => 'datetime',
        'date_deptheadapprove' => 'datetime',
        'date_usercreate_confirm' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
}
