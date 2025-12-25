<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $table = 'departemens'; // nama tabel di DB
    protected $primaryKey = 'id_departemen'; // nama kolom primary key

    protected $fillable = [
        'nama_departemen', // kolom yang boleh diisi lewat create/update
    ];

    public $incrementing = true; // auto increment (karena id pakai bigIncrements)
    protected $keyType = 'int';  // tipe datanya integer

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
