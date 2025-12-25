<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hardware;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class HardwareController extends Controller
{
    // Tampilkan view
    public function index()
    {
        $title = "Data Hardware";
        return view('hardwares.index', compact('title'));
    }

    // Data untuk AJAX
    public function data()
    {
        $hardwares = Hardware::all();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $hardwares
        ]);
    }

    // Create
    public function create(Request $request)
    {
        $request->validate([
            'nama_hardware' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        $hardware = Hardware::create([
            'nama_hardware' => $request->nama_hardware,
            'kategori' => $request->kategori,
            'keterangan' => $request->keterangan
        ]);

        ActivityLogger::log('create', 'Hardware', 'Primary: '.$hardware->id_hardware);

        return response()->json([
            'success' => true,
            'message' => 'Hardware berhasil ditambahkan',
            'data' => $hardware
        ]);
    }

    // Update
    public function update(Request $request)
    {
        $request->validate([
            'id_hardware' => 'required|integer',
            'nama_hardware' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        $hardware = DB::table('hardwares')->where('id_hardware', $request->id_hardware)->first();

        if (!$hardware) {
            return response()->json([
                'success' => false,
                'message' => 'Hardware tidak ditemukan'
            ], 404);
        }

        DB::table('hardwares')
            ->where('id_hardware', $request->id_hardware)
            ->update([
                'nama_hardware' => $request->nama_hardware,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan,
                'updated_at' => now()
            ]);

        ActivityLogger::log('update', 'Hardware', 'Primary: '.$hardware->id_hardware);

        return response()->json([
            'success' => true,
            'message' => 'Hardware berhasil diperbarui',
            'data' => [
                'id_hardware' => $request->id_hardware,
                'nama_hardware' => $request->nama_hardware,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan
            ]
        ]);
    }

    // Delete
    public function destroy($id)
    {
        $hardware = Hardware::find($id);

        if (!$hardware) {
            return response()->json([
                'success' => false,
                'message' => 'Hardware tidak ditemukan'
            ], 404);
        }

        ActivityLogger::log('delete', 'Hardware', 'Primary: '.$hardware->id_hardware);
        $hardware->delete();
        return response()->json([
            'success' => true,
            'message' => 'Hardware berhasil dihapus'
        ]);
    }
}
