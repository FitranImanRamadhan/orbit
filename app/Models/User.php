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

    public static function getDetailByUsername($username)
    {
        if (empty($username)) {
            return null;
        }

        return DB::table('users as u')
            ->leftJoin('plants as p', 'p.id_plant', '=', 'u.plant_id')
            ->leftJoin('departemens as d', 'd.id_departemen', '=', 'u.departemen_id')
            ->leftJoin('positions as pos', 'pos.id_position', '=', 'u.position_id')
            ->select(
                'u.nama_lengkap',
                'p.label as nama_plant',
                'd.nama_departemen',
                'pos.nama_position'
            )
            ->where('u.username', $username)
            ->first();
    }

}
