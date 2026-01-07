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

        //IT
        $isDev              = ($isIT && $user->user_akses === 'developer');
        $isSuperAdmin       = ($isIT && $user->user_akses === 'super_admin');
        $isImplementator    = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [37, 1006]));
        $isTS               = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [34, 32, 1007]));
        $isLeaderImp        = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1006]));
        $isLeaderTs         = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1007]));
        $isAdminIt          = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [34]));
        $isAsmenIt          = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1001]));

        //SELAIN IT
        $isUserNonIT        = (($isNonIT && $user->user_akses === 'user') || $isIT && $user->user_akses === 'user');
        $isAdmin            = ($isNonIT && $user->user_akses === 'admin');

        // List mapping
        $mapping = [
            'developer'         => $isDev,
            'superAdmin'        => $isSuperAdmin,
            'isImplementator'   => $isImplementator,
            'isTS'              => $isTS,
            'isLeaderImp'       => $isLeaderImp,
            'isLeaderTs'        => $isLeaderTs,
            'isAdminIt'         => $isAdminIt,
            'isAsmenIt'         => $isAsmenIt,
            'userNonIT'         => $isUserNonIT,
            'admin'             => $isAdmin,
            'all'               => ($isDev || $isSuperAdmin || $isUserNonIT || $isAdmin),
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
