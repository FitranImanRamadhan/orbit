<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    protected $table = 'softwares';
    protected $primaryKey = 'id_software';

    protected $fillable = [
        'nama_software',
        'modul',
        'keterangan'
    ];

    public $incrementing = true;
    protected $keyType = 'int';
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
