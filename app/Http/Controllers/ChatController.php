<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chat;
use App\Models\Ticketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function canAccess($ticketNo)
    {
        $user = Auth::user();

        // Ambil data tiket
        $ticket = Ticketing::where('ticket_no', $ticketNo)->first();

        if (!$ticket) {
            return false;
        }

        // Jika dia creator tiket → boleh
        if ($ticket->user_create == $user->username) {
            return true;
        }
        // Jika dia approver tiket → boleh
        if ($ticket->approver_level2 == $user->username || $ticket->approver_level3 == $user->username || $ticket->approver_level4 == $user->username) {
            return true;
        }
        if ($user->departemen_id == 3) {
            return true;
        }

        // Selain itu → tolak
        return false;
    }

    public function getChats(Request $request)
    {
        $request->validate([
            'ticket_no' => 'required|string'
        ]);

        $ticketNo = $request->ticket_no;

        // cek akses
        if (!$this->canAccess($ticketNo)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak punya akses'
            ], 403);
        }

        $user = Auth::user();

        $chats = Chat::where('ticket_no', $ticketNo)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) use ($user) {

                $userDetail = User::getDetailByUsername($chat->sender);

                return [
                    'id'          => $chat->id,
                    'sender'      => $chat->sender,
                    'sender_name' => $userDetail->nama_lengkap ?? '-',
                    'is_me'       => $chat->sender == $user->username,
                    'message'     => $chat->message,
                    'file'        => $chat->file_path ? asset('storage/' . $chat->file_path) : null,
                    'time'        => $chat->created_at->format('d-m-Y H:i')
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $chats
        ]);
    }


    public function kirim(Request $request)
    {
        $request->validate([
            'ticket_no' => 'required|string',
            'message'   => 'nullable|string',
            'file'      => 'nullable|file|max:5120'
        ]);

        $ticketNo = $request->ticket_no;

        // Cek hak akses dulu
        if (!$this->canAccess($ticketNo)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak punya akses'
            ], 403);
        }

        $user = Auth::user();

        // Tentukan penerima → khusus approval kirim ke user_create
        $ticket = Ticketing::where('ticket_no', $ticketNo)->first();
        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket tidak ditemukan'
            ], 404);
        }

        // JIKA APPROVAL SUDAH FINAL
        $receiver = [];

        // ==========================
        // JIKA APPROVAL SUDAH FINAL
        // ==========================
        if ($ticket->status_approval === 'approved') {

            // user_create → IT (banyak)
            if ($user->username === $ticket->user_create) {

                $receiver = User::where('departemen_id', 3)
                    ->pluck('username')
                    ->toArray();
            }
            // IT → user_create (1 orang)
            elseif ($user->departemen_id == 3) {

                $receiver = [$ticket->user_create];
            }
        }

        // ==========================
        // JIKA MASIH PROSES APPROVAL
        // ==========================
        elseif ($ticket->status_approval === 'waiting') {

            // user_create → approver aktif
            if ($user->username === $ticket->user_create) {

                if ($ticket->approver_level2 && $ticket->status_level2 === null) {
                    $receiver = [$ticket->approver_level2];
                } elseif ($ticket->approver_level3 && $ticket->status_level3 === null) {
                    $receiver = [$ticket->approver_level3];
                } elseif ($ticket->approver_level4 && $ticket->status_level4 === null) {
                    $receiver = [$ticket->approver_level4];
                }
            }
            // approver → user_create
            else {
                $receiver = [$ticket->user_create];
            }
        }

        // ==========================
        // SAFETY CHECK
        // ==========================
        if (empty($receiver)) {
            return response()->json([
                'status' => false,
                'message' => 'Chat tidak tersedia untuk status tiket ini'
            ], 422);
        }


        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        Chat::create([
            'ticket_no' => $ticketNo,
            'sender'    => $user->username,
            'receiver'  => json_encode($receiver),
            'message'   => $request->message,
            'file_path' => $filePath,
            'is_read'   => false
        ]);

        //foreach notifikasi karena receiver lebih dari 1 jadi harus di foreach
        $receiverUsers = User::whereIn('username', $receiver)
        ->get()
        ->keyBy('username');

    foreach ($receiver as $username) {

        if (!isset($receiverUsers[$username])) {
            continue;
        }

        NotificationHelper::send(
            $ticketNo,
            $username,
            $receiverUsers[$username]->plant_id,
            "Ada pesan baru di tiket $ticketNo"
        );
    }



        return response()->json([
            'status' => true,
            'message' => 'Pesan terkirim'
        ]);
    }
}
