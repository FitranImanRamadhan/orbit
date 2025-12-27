<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use App\Models\UserHirarki;

class UserHirarkiController extends Controller
{
    // menampilkan view
    public function index()
    {
        $title = "User Hirarki";
        return view('userHirarkis.index', compact('title'));
    }

    public function data()
    {
        $hirarkis = UserHirarki::select(
                'user_hirarkis.id_hirarki',
                'user_hirarkis.plant_id',
                'user_hirarkis.departemen_id',
                'plants.nama_plant',
                'departemens.nama_departemen',
                'user_hirarkis.level4 as level4_us',
                'user_hirarkis.level3 as level3_us',
                'user_hirarkis.level2 as level2_us',
                'user_hirarkis.level1 as level1_us',
                'u4.nama_lengkap as level4_name',
                'u3.nama_lengkap as level3_name',
                'u2.nama_lengkap as level2_name'
            )
            ->leftJoin('plants', 'plants.id_plant', '=', 'user_hirarkis.plant_id')
            ->leftJoin('departemens', 'departemens.id_departemen', '=', 'user_hirarkis.departemen_id')
            ->leftJoin('users as u4', 'u4.username', '=', 'user_hirarkis.level4')
            ->leftJoin('users as u3', 'u3.username', '=', 'user_hirarkis.level3')
            ->leftJoin('users as u2', 'u2.username', '=', 'user_hirarkis.level2')
            ->get()
            ->map(function ($row) {
                // Level 1 decode JSON → ambil nama lengkap
                $row->level1_us = $row->level1_us ? json_decode($row->level1_us, true) : [];
                if (!empty($row->level1_us)) {
                    $row->level1_name = User::whereIn('username', $row->level1_us)
                        ->pluck('nama_lengkap')
                        ->toArray();
                } else {
                    $row->level1_name = [];
                }
                return $row;
            });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $hirarkis
        ]);
    }


    public function loadPlant()
    {
        $plant = Plant::select('id_plant', 'nama_plant')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data plant berhasil diambil',
            'data' => $plant
        ]);
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

    public function loadLevel(Request $request)
    {
        $request->validate([
            'plant_id' => 'required|integer',
            'departemen_id' => 'required|integer'
        ]);

        $usersPlant = User::select(
                'users.username',
                'users.nama_lengkap',
                'positions.nama_position',
                'users.departemen_id'
            )
            ->leftJoin('positions', 'positions.id_position', '=', 'users.position_id')
            ->where('users.plant_id', $request->plant_id)
            ->get();

        $usersDept = $usersPlant->where('departemen_id', $request->departemen_id);
        $pos = fn($row) => strtolower(trim($row->nama_position ?? ''));

        // ============== CEK YANG SUDAH ADA DI DATABASE ==============
        $usedLevel2 = DB::table('user_hirarkis')
            ->where('plant_id', $request->plant_id)
            ->where('departemen_id', $request->departemen_id)
            ->pluck('level2')
            ->filter()
            ->toArray();

        $usedLevel1 = DB::table('user_hirarkis')
            ->where('plant_id', $request->plant_id)
            ->where('departemen_id', $request->departemen_id)
            ->pluck('level1')
            ->filter()
            ->map(fn($v) => is_string($v) ? json_decode($v, true) : $v)
            ->flatten()
            ->toArray();

        // ================= FILTER LEVEL ==================
        $level1 = $usersDept->filter(fn($row) =>
            in_array($pos($row), [
                'staff', 'data entry', 'operator', 'npd',
                'programmer system', 'trouble shooting',
                'operation exellent', 'administrator', 'system implementator'
            ]) &&
            !in_array($row->username, $usedLevel1)
        );

        $level2 = $usersDept->filter(fn($row) =>
            in_array($pos($row), [
               'leader', 'leader implementator', 'leader trouble shooting'
            ]) &&
            !in_array($row->username, $usedLevel2)
        );

        $level3 = $usersDept->filter(fn($row) =>
            in_array($pos($row), [
                'ass. manager', 'asst. manager', 'ass.man'
            ])
        );

        $plantManager = $usersPlant->filter(fn($row) => $pos($row) === 'plant manager');
        $level4 = $plantManager->count() > 0
            ? $plantManager
            : $usersPlant->filter(fn($row) => $pos($row) === 'general manager');

        return response()->json([
            'success' => true,
            'data' => [
                'level1' => $level1->values(),
                'level2' => $level2->values(),
                'level3' => $level3->values(),
                'level4' => $level4->values(),
            ]
        ]);
    }


    public function create(Request $request)
    {
        $request->validate([
            'plant_id'      => 'required|integer',
            'departemen_id' => 'required|integer',
            'level1_us'     => 'nullable|array'
        ], [
            'plant_id.required'      => 'Plant wajib dipilih.',
            'departemen_id.required' => 'Departemen wajib dipilih.'
        ]);

        // === CEK DUPLIKASI LEVEL 2 ===
        if ($request->level2_us) {
            $existsLevel2 = DB::table('user_hirarkis as h')
                ->leftJoin('users as u', 'u.username', '=', 'h.level2')
                ->select('u.nama_lengkap')
                ->where('h.plant_id', $request->plant_id)
                ->where('h.level2', $request->level2_us)
                ->first();

            if ($existsLevel2) {
                return response()->json([
                    'success' => false,
                    'message' => "User {$existsLevel2->nama_lengkap} sudah terdaftar sebagai Level 2 di plant yang sama."
                ], 422);
            }
        }

        // === CEK DUPLIKASI LEVEL 1 (JSON) ===
        if ($request->level1_us && is_array($request->level1_us)) {
            foreach ($request->level1_us as $u) {

                $existsLevel1 = DB::table('user_hirarkis as h')
                    ->leftJoin('users as us', DB::raw("'$u'"), '=', 'us.username')
                    ->select('us.nama_lengkap')
                    ->where('h.plant_id', $request->plant_id)
                    ->whereJsonContains('h.level1', $u)
                    ->first();

                if ($existsLevel1) {
                    $nama = $existsLevel1->nama_lengkap ?? $u;
                    return response()->json([
                        'success' => false,
                        'message' => "User {$nama} sudah terdaftar sebagai Level 1 di plant yang sama."
                    ], 422);
                }
            }
        }

        $hirarki = UserHirarki::create([
            'plant_id'      => $request->plant_id,
            'departemen_id' => $request->departemen_id,
            'level4'        => $request->level4_us,
            'level3'        => $request->level3_us,
            'level2'        => $request->level2_us,
            'level1'        => json_encode($request->level1_us)
        ]);
        ActivityLogger::log('create', 'UserHirarki', 'Primary: '.$hirarki->id_hirarki);
        return response()->json([
            'success' => true,
            'message' => 'Data hirarki berhasil ditambahkan.',
            'data' => $hirarki
        ]);
    }


    public function update(Request $request)
    {
        $hirarki = UserHirarki::find($request->id_hirarki);

        if (!$hirarki) {
            return response()->json([
                'success' => false,
                'message' => 'Data hirarki tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'plant_id'      => 'required|integer',
            'departemen_id' => 'required|integer',
            'level1_us'     => 'nullable|array'
        ], [
            'plant_id.required'      => 'Plant wajib dipilih.',
            'departemen_id.required' => 'Departemen wajib dipilih.'
        ]);

        // === CEK DUPLIKASI LEVEL 2 ===
        if ($request->level2_us) {
            $existsLevel2 = DB::table('user_hirarkis as h')
                ->leftJoin('users as u', 'u.username', '=', 'h.level2')
                ->select('u.nama_lengkap')
                ->where('h.plant_id', $request->plant_id)
                ->where('h.level2', $request->level2_us)
                ->where('h.id_hirarki', '!=', $request->id_hirarki)
                ->first();

            if ($existsLevel2) {
                return response()->json([
                    'success' => false,
                    'message' => "User {$existsLevel2->nama_lengkap} sudah terdaftar sebagai Level 2 di plant yang sama."
                ], 422);
            }
        }

        // === CEK DUPLIKASI LEVEL 1 ===
        if ($request->level1_us && is_array($request->level1_us)) {
            foreach ($request->level1_us as $u) {
                $existsLevel1 = DB::table('user_hirarkis as h')
                    ->leftJoin('users as us', DB::raw("'$u'"), '=', 'us.username')
                    ->select('us.nama_lengkap')
                    ->where('h.plant_id', $request->plant_id)
                    ->whereJsonContains('h.level1', $u)
                    ->where('h.id_hirarki', '!=', $request->id_hirarki)
                    ->first();

                if ($existsLevel1) {
                    $nama = $existsLevel1->nama_lengkap ?? $u;
                    return response()->json([
                        'success' => false,
                        'message' => "User {$nama} sudah terdaftar sebagai Level 1 di plant yang sama."
                    ], 422);
                }
            }
        }

        // UPDATE DATA
        $hirarki->update([
            'plant_id'      => $request->plant_id,
            'departemen_id' => $request->departemen_id,
            'level4'        => $request->level4_us,
            'level3'        => $request->level3_us,
            'level2'        => $request->level2_us,
            'level1'        => $request->level1_us ? json_encode($request->level1_us) : null
        ]);
        ActivityLogger::log('update', 'UserHirarki', 'Primary: '.$hirarki->id_hirarki);
        return response()->json([
            'success' => true,
            'message' => 'Data hirarki berhasil diperbarui.',
            'data'    => $hirarki
        ]);
    }

    public function destroy($id_hirarki)
    {
        $userhirarki = UserHirarki::find($id_hirarki);

        if (!$userhirarki) {
            return response()->json([
                'success' => false,
                'message' => 'Userhirarki tidak ditemukan'
            ], 404);
        }
        ActivityLogger::log('delete', 'UserHirarki', 'Primary: '.$userhirarki->id_hirarki);
        $userhirarki->delete();
        return response()->json([
            'success' => true,
            'message' => 'Userhirarki berhasil dihapus'
        ]);
    }


}

