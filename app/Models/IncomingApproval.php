<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingApproval extends Model
{
    use HasFactory;

    protected $table = 'incoming_approvals';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ticket_no',
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

    protected $casts = [
        'status_level2' => 'boolean',
        'status_level3' => 'boolean',
        'date_level2'   => 'datetime',
        'date_level3'   => 'datetime',
    ];
}
