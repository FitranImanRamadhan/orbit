<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccess
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        // Base flags
        $isIT  = ($user->departemen_id == 3);
        $isNonIT = !$isIT;

        $isAdminIT          = ($isIT && $user->user_akses === 'administrator');
        $isSuperIT          = ($isIT && $user->user_akses === 'super_user');
        $isImplementator    = ($isIT && $user->user_akses === 'super_user' && in_array($user->position_id, [34, 37, 1006]));
        $isTS               = ($isIT && $user->user_akses === 'super_user' && in_array($user->position_id, [32, 1007]));
        $isleaderimp        = ($isIT && $user->user_akses === 'super_user' && in_array($user->position_id, [1006]));
        $isleaderts         = ($isIT && $user->user_akses === 'super_user' && in_array($user->position_id, [1007]));
        $isUserNonIT        = (($isNonIT && $user->user_akses === 'user') || $isIT && $user->user_akses === 'user');
        $isSuperNonIT       = ($isNonIT && $user->user_akses === 'super_user');

        // List mapping
        $mapping = [
            'adminIT'         => $isAdminIT,
            'superIT'         => $isSuperIT,
            'isImplementator' => $isImplementator,
            'isTS'            => $isTS,
            'userNonIT'       => $isUserNonIT,
            'superNonIT'      => $isSuperNonIT,
            'adminOrSuperIT'  => ($isAdminIT || $isSuperIT),
            'all'             => ($isAdminIT || $isSuperIT || $isUserNonIT || $isSuperNonIT),
        ];

        // Cek salah satu roles diterima
        foreach ($roles as $role) {
            if (isset($mapping[$role]) && $mapping[$role] === true) {
                return $next($request); // akses diizinkan
            }
        }

        abort(403, 'Maaf, Akses Ditolak.');
    }
}
