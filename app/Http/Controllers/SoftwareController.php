<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Software;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class SoftwareController extends Controller
{
    // Tampilkan view
    public function index()
    {
        $title = "Data Software";
        return view('softwares.index', compact('title'));
    }

    // Data untuk AJAX
    public function data()
    {
        $softwares = Software::all();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $softwares
        ]);
    }

    // Create
    public function create(Request $request)
    {
        $request->validate([
            'nama_software' => 'required|string|max:100',
            
            'keterangan'    => 'nullable|string'
        ]);

        $software = Software::create([
            'nama_software' => $request->nama_software,
            
            'keterangan'    => $request->keterangan
        ]);

        ActivityLogger::log('create', 'Software', 'Primary: ' . $software->id_software);

        return response()->json([
            'success' => true,
            'message' => 'Software berhasil ditambahkan',
            'data' => $software
        ]);
    }

    // Update
    public function update(Request $request)
    {
        $request->validate([
            'id_software'   => 'required|integer',
            'nama_software' => 'required|string|max:100',
            
            'keterangan'    => 'nullable|string'
        ]);

        $software = DB::table('softwares')->where('id_software', $request->id_software)->first();

        if (!$software) {
            return response()->json([
                'success' => false,
                'message' => 'Software tidak ditemukan'
            ], 404);
        }

        DB::table('softwares')
            ->where('id_software', $request->id_software)
            ->update([
                'nama_software' => $request->nama_software,
                
                'keterangan'    => $request->keterangan,
                'updated_at'    => now()
            ]);

        ActivityLogger::log('update', 'Software', 'Primary: ' . $software->id_software);

        return response()->json([
            'success' => true,
            'message' => 'Software berhasil diperbarui',
            'data' => [
                'id_software'   => $request->id_software,
                'nama_software' => $request->nama_software,
                
                'keterangan'    => $request->keterangan
            ]
        ]);
    }

    // Delete
    public function destroy($id)
    {
        $software = Software::find($id);

        if (!$software) {
            return response()->json([
                'success' => false,
                'message' => 'Software tidak ditemukan'
            ], 404);
        }

        ActivityLogger::log('delete', 'Software', 'Primary: ' . $software->id_software);

        $software->delete();

        return response()->json([
            'success' => true,
            'message' => 'Software berhasil dihapus'
        ]);
    }
}
