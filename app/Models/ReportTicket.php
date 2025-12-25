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
        'approver',
        'status_approval',
        'date_approval',
        'status_ticket',
    ];

    protected $casts = [
        'status_approval' => 'boolean',
        'date_approval'   => 'datetime',
    ];

   protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
