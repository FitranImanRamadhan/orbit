<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'tbl_chats'; // Nama tabel

    protected $fillable = [
        'no_ticket',
        'pengirim_username',
        'penerima_username',
        'pesan',
        'status_dibaca',
        'status_dikirim',
    ];

    protected $casts = [
        'status_dibaca' => 'boolean',
        'status_dikirim' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
