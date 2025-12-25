<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;

    protected $table = 'plants'; // nama tabel di DB
    protected $primaryKey = 'id_plant'; // nama kolom primary key

    protected $fillable = [
        'nama_plant', // kolom yang boleh diisi lewat create/update
        'label'
    ];

    public $incrementing = true; // auto increment (karena id pakai bigIncrements)
    protected $keyType = 'int';  // tipe datanya integer

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
