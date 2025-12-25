<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstTicket extends Model
{
    use HasFactory;

    protected $table = 'mst_tickets'; // nama tabel di DB
    protected $primaryKey = 'id_ticket'; // nama kolom primary key

    protected $fillable = [
        'nama_ticket',
        'tipe',
        'user_create'
    ];

    public $incrementing = true; // auto increment (karena id pakai bigIncrements)
    protected $keyType = 'int';  // tipe datanya integer

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
