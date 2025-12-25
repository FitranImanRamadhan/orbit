<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use App\Models\Departemen;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Helpers\ActivityLogger;

class UserController extends Controller
{
    public function profile()
    {
        $title = "Profile";
        return view('users.profile', compact('title'));
    }

    public function profiledata()
    {
        $nik = Auth::user()->nik;
        $user = DB::table('users as u')
            ->leftJoin('plants as p', 'u.plant_id', '=', 'p.id_plant')
            ->leftJoin('departemens as d', 'u.departemen_id', '=', 'd.id_departemen')
            ->leftJoin('positions as pos', 'u.position_id', '=', 'pos.id_position')
            ->select(
                'u.nik',
                'u.nama_lengkap',
                'u.username',
                'u.user_akses',
                'u.avatar',
                DB::raw('p.nama_plant as plant'),
                DB::raw('d.nama_departemen as departemen'),
                DB::raw('pos.nama_position as position')
            )
            ->where('u.nik', $nik)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
{
    $username = $request->username; // username lama (tetap)
    $data = [
        'nama_lengkap' => $request->nama_lengkap
    ];

    // update password jika diisi
    if (!empty($request->password)) {
        $data['password'] = Hash::make($request->password);
    }

    // update avatar jika ada
    if ($request->hasFile('avatar')) {
        $avatar = $request->file('avatar');
        $ext = $avatar->getClientOriginalExtension();
        $avatarName = $username . '.' . $ext;
        $avatar->move(public_path('assets/img/avatar'), $avatarName);
        $data['avatar'] = $avatarName;
    }

    $updated = DB::table('users')
        ->where('username', $username)
        ->update($data);

    return response()->json([
        'success' => $updated ? true : false,
        'message' => $updated ? 'Profile berhasil diperbarui' : 'Tidak ada data yang berubah'
    ]);
}




    public function index()
    {
        $title = "Data User";
        return view('users.index', compact('title'));
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

    public function loadSect(Request $request)
    {
        $departemen_id = $request->input('departemen_id');
        if (!$departemen_id) {
            return response()->json([
                'success' => false,
                'message' => 'Departemen ID tidak ditemukan',
                'data' => []
            ]);
        }
        $sect = Position::select('id_position', 'nama_position')
            ->where('departemen_id', $departemen_id)
            ->orderBy('nama_position')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data position berhasil diambil',
            'data' => $sect
        ]);
    }


    public function data()
    {
        $user = User::select(
            'users.id_user',
            'users.nik',
            'users.nama_lengkap',
            'users.plant_id',
            'users.departemen_id',
            'users.position_id',
            'users.username',
            'users.password',
            'users.user_akses',
            'plants.nama_plant',
            'departemens.nama_departemen',
            'positions.nama_position'
        )->leftjoin('plants', 'plants.id_plant', '=', 'users.plant_id')
            ->leftjoin('departemens', 'departemens.id_departemen', '=', 'users.departemen_id')
            ->leftjoin('positions', 'positions.id_position', '=', 'users.position_id')
            ->get();
        $user->makeVisible('password');
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $user
        ]);
    }

    public function data_dblink()
    {
        $conn = sprintf(
            "host=%s dbname=%s user=%s password=%s",
            env('DBLINK_HOST'),
            env('DBLINK_DB'),
            env('DBLINK_USER'),
            env('DBLINK_PASS')
        );
        $query = "SELECT *
                FROM dblink( '$conn',
                    'SELECT mp.nik, mp.id_department, mp.id_position, mp.full_name AS nama_lengkap, mp.status,
                            dp.department AS nama_departemen, ps.position AS nama_position, us.username, us.password
                    FROM tbn_man_power mp
                    LEFT JOIN tbn_department dp ON mp.id_department = dp.id_department
                    LEFT JOIN tbn_position ps ON mp.id_position = ps.id_position
                    LEFT JOIN tbn_users us ON mp.nik = us.nik
                    WHERE status = TRUE'
                )
                AS remote_data(
                    nik VARCHAR, id_department varchar, id_position varchar, nama_lengkap VARCHAR, status VARCHAR,
                    nama_departemen VARCHAR,  nama_position VARCHAR,  username VARCHAR, password VARCHAR
                );
            ";
        $data = DB::select($query);

        return response()->json([
            'success' => true,
            'message' => 'Data DBLink berhasil diambil',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nik'            => [
                'required',
                'string',
                'max:100',
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('plant_id', $request->plant_id);
                }),
            ],
            'nama_lengkap'   => 'required|string|max:100',
            'plant_id'       => 'required|integer',
            'departemen_id'  => 'required|integer',
            'position_id'    => 'required|integer',
            'username'       => 'required|string|max:100|unique:users,username',
            'password'       => 'required|string|max:100',
            'user_akses'     => 'required|string|max:100',
        ], [
            'nik.required'           => 'NIK wajib diisi.',
            'nik.unique'             => 'NIK sudah terdaftar di plant yang sama.',
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'plant_id.required'      => 'Plant wajib dipilih.',
            'departemen_id.required' => 'Departemen wajib dipilih.',
            'username.required'      => 'Username wajib diisi.',
            'username.unique'        => 'Username sudah dipakai, coba username lain.',
            'password.required'      => 'Password wajib diisi.',
            'user_akses.required'    => 'Akses user wajib diisi.',
        ]);

        $user = User::create([
            'nik'           => $request->nik,
            'nama_lengkap'  => $request->nama_lengkap,
            'plant_id'      => $request->plant_id,
            'departemen_id' => $request->departemen_id,
            'position_id'   => $request->position_id,
            'username'      => $request->username,
            'password'      => Hash::make($request->password),
            'user_akses'    => $request->user_akses
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ]);
    }


    public function update(Request $request, $id_user)
{
    $user = User::where('id_user', $id_user)->firstOrFail();

    // Simpan username lama
    $oldUsername = $user->username;

    $request->validate([
        'nik'           => [
            'required',
            'string',
            'max:100',
            Rule::unique('users')->ignore($user->id_user, 'id_user')
                ->where(function ($query) use ($request) {
                    return $query->where('plant_id', $request->plant_id);
                }),
        ],
        'nama_lengkap'  => 'required|string|max:100',
        'plant_id'      => 'required|integer',
        'departemen_id' => 'required|integer',
        'position_id'   => 'nullable|integer',
        'username'      => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
        'user_akses'    => 'required|string|max:100',
        'password'      => 'nullable|string|max:50'
    ]);

    $user->nik           = $request->nik;
    $user->nama_lengkap  = $request->nama_lengkap;
    $user->username      = $request->username;
    $user->plant_id      = $request->plant_id;
    $user->departemen_id = $request->departemen_id;
    $user->position_id   = $request->position_id;
    $user->user_akses    = $request->user_akses;

    if (!empty($request->password)) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    // 🔥 Update semua kolom terkait username di tbl_tickets
    // $hirarkis = DB::table('user_hirarkis')
    //     ->where('level2', $oldUsername)
    //     ->orWhere('level3', $oldUsername)
    //     ->orWhere('level4', $oldUsername)
    //     ->orWhereJsonContains('level1', $oldUsername)
    //     ->get();

    // foreach ($hirarkis as $h) {

    //     // --- Update level1 (JSON Array) ---
    //     $level1 = json_decode($h->level1, true) ?? [];

    //     // Replace oldUsername → new username
    //     $level1 = array_map(function ($val) use ($oldUsername, $user) {
    //         return trim(strtolower($val)) === trim(strtolower($oldUsername))
    //             ? $user->username
    //             : $val;
    //     }, $level1);

    //     // Update ke DB
    //     DB::table('user_hirarkis')
    //         ->where('id_hirarki', $h->id_hirarki)
    //         ->update([
    //             'level1' => json_encode($level1),
    //             'level2' => trim(strtolower($h->level2)) === trim(strtolower($oldUsername)) ? $user->username : $h->level2,
    //             'level3' => trim(strtolower($h->level3)) === trim(strtolower($oldUsername)) ? $user->username : $h->level3,
    //             'level4' => trim(strtolower($h->level4)) === trim(strtolower($oldUsername)) ? $user->username : $h->level4,
    //         ]);
    // }



    // // ===============================
    // // UPDATE DI TBL_TICKETS — version BENAR
    // // ===============================
    // DB::table('tbl_tickets')
    // ->where('user_create', $oldUsername)
    // ->update(['user_create' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('approver_level2', $oldUsername)
    //     ->update(['approver_level2' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('approver_level3', $oldUsername)
    //     ->update(['approver_level3' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('approver_level4', $oldUsername)
    //     ->update(['approver_level4' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('approver_depthead', $oldUsername)
    //     ->update(['approver_depthead' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('it_start', $oldUsername)
    //     ->update(['it_start' => $user->username]);

    // DB::table('tbl_tickets')
    //     ->where('it_finish', $oldUsername)
    //     ->update(['it_finish' => $user->username]);



    return response()->json([
        'success' => true,
        'message' => 'User berhasil diperbarui'
    ]);
}



    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
