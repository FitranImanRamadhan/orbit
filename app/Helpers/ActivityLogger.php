<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $module, $description = null)
    {
        ActivityLog::create([
            'user_id'   => Auth::user()?->id_user,
            'action'     => $action,
            'module'     => $module,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
