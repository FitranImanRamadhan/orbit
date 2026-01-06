<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTicket extends Model
{
    protected $table = 'report_tickets';

    protected $fillable = [
        'year',
        'month',
        'week',
        'jenis_ticket',

        'user_create',

        'approver_level2',
        'approver_level3',

        'status_level2',
        'status_level3',

        'date_level2',
        'date_level3',

        'status_ticket',
    ];

    /**
     * Cast tipe data
     */
    protected $casts = [
        'status_level2' => 'boolean',
        'status_level3' => 'boolean',

        'date_level2' => 'datetime',
        'date_level3' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
