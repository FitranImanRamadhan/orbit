<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHirarki extends Model
{
    use HasFactory;

    // Nama tabel jika tidak sesuai konvensi Laravel (plural lowercase)
    protected $table = 'user_hirarkis';

    // Primary key
    protected $primaryKey = 'id_hirarki';

    // Jika primary key bukan auto-increment integer, atur $incrementing = false
    public $incrementing = true;

    // Tipe primary key
    protected $keyType = 'int';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'plant_id',
        'departemen_id',
        'level5',
        'level4',
        'level3',
        'level2',
        'level1',
    ];

    // Relasi ke plant
    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'id_plant');
    }

    // Relasi ke departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'id_departemen');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
