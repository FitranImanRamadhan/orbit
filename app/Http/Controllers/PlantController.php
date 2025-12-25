<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use App\Helpers\ActivityLogger;

class PlantController extends Controller
{
    // menampilkan view halaman index
    public function index()
    {
        $title = "Data Plant";
        return view('plants.index', compact('title'));
    }

    // mengambil data untuk frontend (DataTables / AJAX)
    public function data()
    {
        $plants = Plant::all();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $plants
        ]);
    }

    // menyimpan plant baru
    public function create(Request $request)
    {
        $request->validate([
            'nama_plant' => 'required|string|max:100|unique:plants,nama_plant',
            'label' => 'nullable|string|max:3'
        ]);

        $plant = Plant::create([
            'nama_plant' => $request->nama_plant,
            'label' => $request->label
        ]);

        ActivityLogger::log('create', 'Plant', 'Primary: '.$plant->id_plant);
        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil ditambahkan',
            'data' => $plant
        ]);
    }

    // mengupdate plant
    public function update(Request $request)
    {
        $request->validate([
            'id_plant' => 'required|integer',
            'nama_plant' => 'required|string|max:100',
            'label' => 'nullable|string|max:3'
        ]);

        $plant = Plant::find($request->id_plant);

        if (!$plant) {
            return response()->json([
                'success' => false,
                'message' => 'Plant tidak ditemukan'
            ], 404);
        }

        $plant->nama_plant = $request->nama_plant;
        $plant->label = $request->label;
        $plant->save();

        ActivityLogger::log('update', 'Plant', 'Primary: '.$plant->id_plant);

        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil diperbarui',
            'data' => $plant
        ]);
    }

    // menghapus plant
    public function destroy($id)
    {
        $plant = Plant::find($id);

        if(!$plant){
            return response()->json([
                'success' => false,
                'message' => 'Plant tidak ditemukan'
            ], 404);
        }
        ActivityLogger::log('delete','Plant', 'Primary: '.$plant->id_plant );
        $plant->delete();
        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil dihapus'
        ]);
    }
}
