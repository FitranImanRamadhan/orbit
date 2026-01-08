<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Ticketing; // misal nama model ticket
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    // Ambil notif list
   public function getNotifications()
    {
        $user = Auth::user();

        // Cek apakah pembersihan sudah dilakukan hari ini
        $cacheKey = 'notif_cleanup_done_' . $user->username . '_' . $user->plant_id;
        // Jika belum pernah dibersihkan hari ini → lakukan pembersihan
        if (!Cache::has($cacheKey)) {

            Notification::where('username', $user->username)
                ->where('plant_id', $user->plant_id)
                // ->where('status', 'read')
                ->where('updated_at', '<', now()->subDays(14))
                ->delete();

            // Tandai bahwa cleanup sudah dilakukan hari ini
            Cache::put($cacheKey, true, now()->addDay());
        }

        // Ambil semua notifikasi unread (limit dulu gapapa)
        $notifications = Notification::where('username', $user->username)
            ->where('plant_id', $user->plant_id)
            ->where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $validNotifications = [];
        $invalidIds = [];

        foreach ($notifications as $notif) {
            $userDetail = User::getDetailByUsername($notif->username);
            $notif->nama_lengkap = $userDetail->nama_lengkap ?? '-';

            // ✅ JIKA REPORT TICKET → LANGSUNG VALID
            if (str_starts_with($notif->ticket_no, 'RPT/')) {
                $validNotifications[] = $notif;
                continue;
            }

            // Ticket normal → cek ke tbl_tickets
            $ticketExists = DB::table('tbl_tickets')
                ->where('ticket_no', $notif->ticket_no)
                ->exists();

            if ($ticketExists) {
                $validNotifications[] = $notif;
            } else {
                $invalidIds[] = $notif->id;
            }
        }

        // Hapus hanya yang benar-benar invalid
        if (!empty($invalidIds)) {
            Notification::whereIn('id', $invalidIds)->delete();
        }

        return response()->json([
            'count' => count($validNotifications),
            'notifications' => $validNotifications
        ]);

    }


    // Mark read tanpa parameter di route
    public function markRead(Request $request)
    {
        Notification::where('id', $request->id)->update(['status' => 'read']);

        return response()->json(['success' => true]);
    }

    public function checkRedirect(Request $request)
    {
        $ticketNo = $request->ticket;
        $user = Auth::user();

        $ticket = Ticketing::where('ticket_no', $ticketNo)->first();
        if (!$ticket) {return response()->json(['redirect' => false]);}

        if ($ticket->approver_depthead == $user->username || $ticket->approver_level4 == $user->username ||
            $ticket->approver_level3 == $user->username || $ticket->approver_level2 == $user->username) {
            return response()->json(['redirect' => true, 'url' => route('ticketing.approval')]);
        }

        // User lain -> tidak redirect
        return response()->json(['redirect' => false]);
    }
}
