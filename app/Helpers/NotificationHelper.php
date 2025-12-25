<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    public static function send($ticketNo, $username, $plantId, $message)
    {
        return Notification::create([
            'ticket_no' => $ticketNo,
            'username'  => $username,
            'plant_id'  => $plantId,
            'message'   => $message,
            'status'    => 'unread'
        ]);
    }

    public static function markAsRead($id)
    {
        $notif = Notification::find($id);
        if ($notif) {
            $notif->update([
                'status'  => 'read'
            ]);
        }
    }

}
