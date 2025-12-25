<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions'; // nama tabel di DB
    protected $primaryKey = 'id_position'; // nama kolom primary key

    protected $fillable = [
        'departemen_id',
        'nama_position' // kolom yang boleh diisi lewat create/update
    ];

    public $incrementing = true; // auto increment (karena id pakai bigIncrements)
    protected $keyType = 'int';  // tipe datanya integer

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
