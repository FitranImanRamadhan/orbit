<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstTicket;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class MstTicketController extends Controller
{
    // menampilkan view
    public function index()
    {
        $title = "Data Ticket";
        return view('tickets.index', compact('title'));
    }
    
    public function data()
    {
        $ticket = MstTicket::all();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $ticket
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama_ticket' => 'required|string|max:255',
            'tipe' => 'required|string'
        ]);

        $ticket = MstTicket::create([
            'nama_ticket' => $request->nama_ticket,
            'tipe' => $request->tipe,
            'user_create' => Auth::user()->username
        ]);
        ActivityLogger::log('create','Ticket', 'Primary: '.$ticket->id_ticket );
        return response()->json([
            'success' => true,
            'message' => 'Ticket berhasil ditambahkan',
            'data' => $ticket
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_ticket' => 'required|integer',
            'nama_ticket' => 'required|string|max:255',
            'tipe' => 'required|string'
        ]);

        $ticket = DB::table('mst_tickets')->where('id_ticket', $request->id_ticket)->first();
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket tidak ditemukan'
            ], 404);
        }

        // Update data
        DB::table('mst_tickets')
            ->where('id_ticket', $request->id_ticket)
            ->update([
                'nama_ticket' => $request->nama_ticket,
                'tipe' => $request->tipe,
                'updated_at' => now()
            ]);
        ActivityLogger::log('update','Ticket', 'Primary: '.$ticket->id_ticket );  

        return response()->json([
            'success' => true,
            'message' => 'Ticket berhasil diperbarui',
            'data' => [
                'id_ticket' => $request->id_ticket,
                'nama_ticket' => $request->nama_ticket,
                'tipe' => $request->tipe
            ]
        ]);
    }


    public function destroy($id)
    {
        $ticket = MstTicket::find($id);

        if(!$ticket){
            return response()->json([
                'success' => false,
                'message' => 'Ticket tidak ditemukan'
            ], 404);
        }
        ActivityLogger::log('delete','Ticket', 'Primary: '.$ticket->id_ticket );
        $ticket->delete();
        return response()->json([
            'success' => true,
            'message' => 'Ticket berhasil dihapus'
        ]);
    }
}

