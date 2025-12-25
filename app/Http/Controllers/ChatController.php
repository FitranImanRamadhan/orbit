<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Cek level user pembuat ticket
     */
    private function getUserCreateLevel($ticketNo)
    {
        $ticket = DB::table('tbl_tickets')->where('ticket_no', $ticketNo)->first();
        if (!$ticket) return null;

        $userCreate = DB::table('users')->where('username', $ticket->user_create)->first();
        if (!$userCreate) return null;

        $hirarki = DB::table('user_hirarkis')
                    ->where('plant_id', $userCreate->plant_id)
                    ->where('departemen_id', $userCreate->departemen_id)
                    ->first();
        if (!$hirarki) return null;

        $level1_users = json_decode($hirarki->level1, true) ?: [];

        if (in_array($userCreate->username, $level1_users)) return 1;
        elseif ($userCreate->username == $hirarki->level2) return 2;
        elseif ($userCreate->username == $hirarki->level3) return 3;
        elseif ($userCreate->username == $hirarki->level4) return 4;

        return null;
    }

    /**
     * Kirim pesan chat
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'no_ticket' => 'required|string',
            'pesan' => 'required|string',
            'penerima_username' => 'nullable|string' // null = semua bisa melihat
        ]);

        $chat = Chat::create([
            'no_ticket' => $request->no_ticket,
            'pengirim_username' => Auth::user()->username,
            'penerima_username' => $request->penerima_username,
            'pesan' => $request->pesan,
            'status_dibaca' => false,
            'status_dikirim' => true
        ]);

        return response()->json([
            'success' => true,
            'chat' => $chat
        ]);
    }

    /**
     * Ambil semua chat terkait ticket
     */
    public function getMessages(Request $request)
    {
        $ticketNo = $request->ticket_no;
        $loginUser = Auth::user()->username;

        $ticket = DB::table('tbl_tickets')->where('ticket_no', $ticketNo)->first();
        if (!$ticket) return response()->json(['success'=>false, 'message'=>'Ticket tidak ditemukan'], 404);

        // Ambil semua chat untuk ticket ini
        $chats = Chat::where('no_ticket', $ticketNo)
            ->orderBy('created_at', 'asc')
            ->get();

        // Update chat yang belum dibaca untuk login user
        Chat::where('no_ticket', $ticketNo)
            ->where('penerima_username', $loginUser)
            ->where('status_dibaca', false)
            ->update(['status_dibaca' => true]);

        return response()->json([
            'success' => true,
            'data' => $chats
        ]);
    }
}
