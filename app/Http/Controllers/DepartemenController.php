<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class DepartemenController extends Controller
{
    // menampilkan view
    public function index()
    {
        $title = "Data Departemen";
        return view('departemens.index', compact('title'));
    }
    
    public function data()
    {
        $departemens = Departemen::all();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $departemens
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:60'
        ]);

        // cek nama departemen (case-insensitive)
        $exists = Departemen::whereRaw(
            'LOWER(nama_departemen) = ?',
            [strtolower($request->nama_departemen)]
        )->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Nama departemen sudah terdaftar',
                'data' => null
            ]);
        }

        $departemen = Departemen::create([
            'nama_departemen' => $request->nama_departemen
        ]);
        ActivityLogger::log('create','Departemen', 'Primary: '.$departemen->id_departemen );
        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil ditambahkan',
            'data' => $departemen
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_departemen' => 'required|integer',
            'nama_departemen' => 'required|string|max:60'
        ]);

        $departemen = DB::table('departemens')->where('id_departemen', $request->id_departemen)->first();

        if (!$departemen) {
            return response()->json([
                'success' => false,
                'message' => 'Departemen tidak ditemukan'
            ], 404);
        }

        $exists = DB::table('departemens')
        ->whereRaw('LOWER(nama_departemen) = ?', [strtolower($request->nama_departemen)])
        ->where('id_departemen', '!=', $request->id_departemen)
        ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Nama departemen sudah digunakan',
                'data' => null
            ]);
        }

        // Update data
        DB::table('departemens')
            ->where('id_departemen', $request->id_departemen)
            ->update([
                'nama_departemen' => $request->nama_departemen,
                'updated_at' => now()
            ]);
        ActivityLogger::log('update','Departemen', 'Primary: '.$departemen->id_departemen );  
        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil diperbarui',
            'data' => [
                'id_departemen' => $request->id_departemen,
                'nama_departemen' => $request->nama_departemen
            ]
        ]);
    }


    public function destroy($id)
    {
        $departemen = Departemen::find($id);

        if(!$departemen){
            return response()->json([
                'success' => false,
                'message' => 'Departemen tidak ditemukan'
            ], 404);
        }
        ActivityLogger::log('delete','Departemen', 'Primary: '.$departemen->id_departemen );
        $departemen->delete();
        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dihapus'
        ]);
    }
}

