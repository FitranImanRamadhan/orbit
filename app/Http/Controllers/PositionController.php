<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class PositionController extends Controller
{
    public function index()
    {
        $title = "Data Position";
        return view('positions.index', compact('title'));
    }

    public function loadDept()
    {
        $dept = Departemen::select('id_departemen', 'nama_departemen')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data departemen berhasil diambil',
            'data' => $dept
        ]);
    }

    public function data()
    {
       $position = Position::select(
            'positions.id_position',
            'positions.departemen_id',
            'positions.nama_position',
            'departemens.nama_departemen'
            )->leftjoin('departemens', 'departemens.id_departemen', '=', 'positions.departemen_id')
            ->get();

       return response()->json([
            'succes => true', 
            'message' => 'Data berhasil diambil',
            'data' => $position
       ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'departemen_id' => 'integer',
            'nama_position' => 'required|string|max:60'
        ]);

        $position = Position::create([
            'departemen_id' => $request->departemen_id,
            'nama_position' => $request->nama_position
        ]);
        ActivityLogger::log('create','Position', 'Primary: '.$position->id_position);
        return response()->json([
            'success' => true,
            'message' => 'Position berhasil ditambahkan',
            'data' => $position
        ]);
    }

    

    public function update(Request $request)
    {
        $request->validate([
            'id_position' => 'required|integer',
            'departemen_id' => 'required|integer',
            'nama_position' => 'required|string|max:60'
        ]);

        $position = DB::table('positions')->where('id_position', $request->id_position)->first();
        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'Position tidak ditemukan'
            ], 404);
        }

        // Update data
        DB::table('positions')
            ->where('id_position', $request->id_position)
            ->update([
                'departemen_id'=> $request->departemen_id,
                'nama_position' => $request->nama_position,
                'updated_at' => now()
            ]);
        ActivityLogger::log('update','Position', 'Primary: '.$position->id_position);
        return response()->json([
            'success' => true,
            'message' => 'Position berhasil diperbarui',
            'data' => [
                'id_position' => $request->id_position,
                'departemen_id' => $request->departemen_id,
                'nama_position' => $request->nama_position
            ]
        ]);
    }


    public function destroy($id)
    {
        $position = Position::find($id);

        if(!$position){
            return response()->json([
                'success' => false,
                'message' => 'Position tidak ditemukan'
            ], 404);
        }

        $position->delete();
        ActivityLogger::log('delete','Position', 'Primary: '.$position->id_position);
        return response()->json([
            'success' => true,
            'message' => 'Position berhasil dihapus'
        ]);
    }
}

