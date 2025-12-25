<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'plant_id',
        'departemen_id',
        'position_id',
        'username',
        'password',
        'user_akses',
    ];

    protected $hidden = ['password'];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
