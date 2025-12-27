<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    use HasFactory;

    protected $table = 'hardwares';
    protected $primaryKey = 'id_hardware';

    protected $fillable = [
        'nama_hardware',
        'kategori',
        'keterangan'
    ];

    public $incrementing = true;
    protected $keyType = 'int';
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

