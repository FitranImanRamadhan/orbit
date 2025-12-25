<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\User;
use App\Models\Ticketing;
use App\Models\UserHirarki;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use App\Models\Hardware;
use App\Models\Software;
use App\Helpers\NotificationHelper;

class TicketingController extends Controller
{
    //get
    public function getHardware()
    {
        $dept = Hardware::select('id_hardware', 'nama_hardware')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data departemen berhasil diambil',
            'data' => $dept
        ]);
    }
    public function getSoftware()
    {
        $dept = Software::select('id_software', 'nama_software')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data departemen berhasil diambil',
            'data' => $dept
        ]);
    }
    public function getDept()
    {
        $dept = Departemen::select('id_departemen', 'nama_departemen')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data departemen berhasil diambil',
            'data' => $dept
        ]);
    }
    public function getDeptHead()
    {
        $plantId = Auth::user()->plant_id;
        $departemenId = Auth::user()->departemen_id;

        $usersPlant = User::select(
            'users.username',
            'users.nama_lengkap',
            'positions.nama_position',
            'users.departemen_id'
        )
            ->leftJoin('positions', 'positions.id_position', '=', 'users.position_id')
            ->where('users.plant_id', $plantId)
            ->get();
        $usersDept = $usersPlant->where('departemen_id', $departemenId);
        $pos = fn($row) => strtolower(trim($row->nama_position ?? ''));

        $deptHead = $usersDept->filter(
            fn($row) =>
            in_array($pos($row), [
                'ass. manager',
                'asst. manager',
                'ass.man'
            ])
        );

        return response()->json([
            'success' => true,
            'data' => [
                'depthead' => $deptHead->values(),
            ]
        ]);
    }

    //create ticket
    public function create_ticket()
    {
        return view('ticketings.create_ticket');
    }
    public function create_ticket_proses(Request $request)
    {

        DB::beginTransaction(); // ← BEGIN

        try {
            $request->validate([
                'jenisTicket' => 'required|string',
                'tglPermintaan' => 'required|date',
                'deskripsi' => 'nullable|string',
                'dept_us' => 'nullable|string',
                'kategoriKlaim' => 'nullable|string',
                'item_ticket' => 'nullable|integer',
                'file1' => 'nullable|file',
                'file2' => 'nullable|file',
                'file3' => 'nullable|file',
                'priority' => 'nullable|string',
            ]);
            // dd($request->all());
            // 2. Ambil data user login
            $username = Auth::user()->username;
            $plantId = Auth::user()->plant_id;
            $departemenId = Auth::user()->departemen_id;
            // 3. Ambil hirarki sesuai plant & departemen
            $hirarki = DB::table('user_hirarkis')
                ->where('plant_id', $plantId)
                ->where('departemen_id', $departemenId)
                ->where(function ($q) use ($username) {
                    $q->where('level2', $username)
                        ->orWhere('level3', $username)
                        ->orWhere('level4', $username)
                        ->orWhereJsonContains('level1', $username);
                })
                ->first();
            if (!$hirarki) {
                return response()->json(['message' => 'Hirarki user tidak ditemukan untuk plant dan departemen anda']);
            }
            // Inisialisasi approvers default
            $approvers = [
                'approver_level2'   => null,
                'approver_level3'   => null,
                'approver_level4'   => null,
                'status_level2'     => null,
                'status_level3'     => null,
                'status_level4'     => null,
                'date_level2'       => null,
                'date_level3'       => null,
                'date_level4'       => null,
            ];
            // Cek jenisTicket
            $user_level = null;
            if (strtolower($request->jenisTicket) == 'software' || strtolower($request->jenisTicket) == 'hardware') {

                // Level1 berupa array JSON
                $level1_users = json_decode($hirarki->level1, true);
                // Tentukan level user login
                if (in_array($username, $level1_users)) {
                    $user_level    = 1;
                } elseif ($username == $hirarki->level2) {
                    $user_level   = 2;
                } elseif ($username == $hirarki->level3) {
                    $user_level   = 3;
                } elseif ($username == $hirarki->level4) {
                    $user_level   = 4;
                }
                // Tentukan approver berdasarkan level user login
                switch ($user_level) {
                    case 1:
                        $approvers['approver_level2'] = $hirarki->level2;
                        $approvers['approver_level3'] = $hirarki->level3;
                        $approvers['approver_level4'] = $hirarki->level4;
                        break;
                    case 2:
                        $approvers['approver_level3'] = $hirarki->level3;
                        $approvers['approver_level4'] = $hirarki->level4;
                        break;
                    case 3:
                        $approvers['approver_level4'] = $hirarki->level4;
                        break;
                    case 4:
                        $approvers['approver_level4'] = $username;
                        $approvers['status_level4'] = true;
                        $approvers['date_level4'] = now();
                        break;
                    default:
                        dd($username, $hirarki->level1, $hirarki->level2, $hirarki->level3, $hirarki->level4);
                        return response()->json(['message' => 'Username tidak ada di hirarki'], 400);
                }
            } else {
                // Kalau hardware, semua level approver diganti '-'
                $approvers['approver_level2']   = '-';
                $approvers['approver_level3']   = '-';
                $approvers['approver_level4']   = '-';
                $approvers['status_level2']     = null;
                $approvers['status_level3']     = null;
                $approvers['status_level4']     = null;
                $approvers['date_level2']       = null;
                $approvers['date_level3']       = null;
                $approvers['date_level4']       = null;
            }

            // 1. Generate no ticket
            $plantId = Auth::user()->plant_id;
            $plant = DB::table('plants')->where('id_plant', $plantId)->first();
            $label_plant = $plant->label ?? 'UNKNOWN';
            $ticketTypeCode = strtolower($request->jenisTicket) == 'software' ? 'SW' : 'HW';
            $today = now()->format('Y-m-d');
            $day = now()->format('d');
            $month = now()->format('m');
            $year = now()->format('y');
            $lastTicketToday = DB::table('tbl_tickets')
                ->whereDate('created_at', $today)
                ->where('jenis_ticket', $request->jenisTicket)
                ->orderBy('id', 'desc')
                ->first();
            $lastNumber = 0;
            if ($lastTicketToday) {
                $parts = explode('/', $lastTicketToday->ticket_no);
                $lastNumber = (int) end($parts);
            }
            $runningNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $ticket_no = "TCK/{$label_plant}/{$ticketTypeCode}/{$day}/{$month}/{$year}/{$runningNumber}";

            // 6. Insert ticket
            $ticketId = DB::table('tbl_tickets')->insertGetId([
                'ticket_no'          => $ticket_no,
                'jenis_ticket'       => $request->jenisTicket,
                'item_ticket'        => $request->item_ticket,
                'tgl_permintaan'     => $request->tglPermintaan,
                'kategori_klaim'     => $request->kategoriKlaim,
                'deskripsi'          => $request->deskripsi,
                'approver_depthead'  => $request->dept_us,
                'user_create'         => $username,
                'approver_level2'    => $approvers['approver_level2'],
                'approver_level3'    => $approvers['approver_level3'],
                'approver_level4'    => $approvers['approver_level4'],
                'status_level2'      => $approvers['status_level2'],
                'status_level3'      => $approvers['status_level3'],
                'status_level4'      => $approvers['status_level4'],
                'date_level2'        => $approvers['date_level2'],
                'date_level3'        => $approvers['date_level3'],
                'date_level4'        => $approvers['date_level4'],
                'priority'           => $request->priority ?? 'medium',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 10. Handle file upload
            foreach (['file1', 'file2', 'file3'] as $index => $fileField) {
                if ($request->hasFile($fileField)) {
                    $file = $request->file($fileField);
                    $plantlabel = $label_plant;
                    $date = now()->format('Ymd');
                    $runningNumber = str_pad($index + 1, 4, '0', STR_PAD_LEFT); // No urut sederhana, 1,2,3 per upload
                    $filename = "TCK_{$plantlabel}_{$date}_{$runningNumber}." . $file->getClientOriginalExtension(); // Nama file
                    $file->move(public_path('assets/upload/lampiran/'), $filename); // Uploa
                    // Simpan path ke DB
                    Ticketing::where('id', $ticketId)->update([$fileField => 'assets/upload/lampiran/' . $filename]);
                }
            }

            $approval_flow = [
                2 => $approvers['approver_level2'],
                3 => $approvers['approver_level3'],
                4 => $approvers['approver_level4'],
            ];

            $next_approver = null;

            foreach ($approval_flow as $level => $approver) {
                if ($level > $user_level && !empty($approver) && $approver !== '-') {
                    $next_approver = $approver;
                    break;
                }
            }
            ActivityLogger::log('create', 'Ticket', 'Primary: ' . $ticket_no);
            if (in_array(strtolower($request->jenisTicket), ['software', 'hardware'])) {
                $message = "Ticket baru ($ticket_no) menunggu approval.";
                if ($next_approver) {
                    // kirim ke approver berikutnya
                    NotificationHelper::send(
                        $ticket_no,
                        $next_approver,
                        $plantId,
                        $message
                    );
                } else {
                    // tidak ada approver lagi → auto approve
                    NotificationHelper::send(
                        $ticket_no,
                        $username,
                        $plantId,
                        "Ticket ($ticket_no) otomatis disetujui karena Anda berada di level tertinggi approval."
                    );
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket berhasil dibuat',
                'ticket_id' => $ticketId,
                'user_level' => $user_level
            ]);
        } catch (\Exception $e) {

            DB::rollBack(); // ← ROLLBACK kalau ada error

            return response()->json([
                'message' => 'Gagal membuat ticket: ' . $e->getMessage(),
            ], 500);
        }
    }

    //approval
    public function approval()
    {
        return view('ticketings.approval');
    }
    public function data_approval(Request $request)
    {
        $username = Auth::user()->username;
        $plantId = Auth::user()->plant_id;
        $departemenId = Auth::user()->departemen_id;
        $is_level4 = UserHirarki::where('level4', $username)->exists();

        $query = Ticketing::from('tbl_tickets as a')
            ->leftJoin('users as b', 'a.user_create', '=', 'b.username')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('plants as f', 'b.plant_id', '=', 'f.id_plant')
            ->leftJoin('user_hirarkis as h', function ($join) {
                $join->on('h.plant_id', 'b.plant_id')
                    ->on('h.departemen_id', '=', 'b.departemen_id');
            })
            ->leftJoin('hardwares as d', 'a.item_ticket', '=', 'd.id_hardware')
            ->leftJoin('softwares as e', 'a.item_ticket', '=', 'e.id_software')
            ->selectRaw("
                    DISTINCT ON (a.ticket_no)
                    a.*,
                    b.nama_lengkap,
                    c.nama_departemen,
                    f.nama_plant,
                    h.level1, h.level2, h.level3, h.level4,
                    CASE 
                        WHEN a.jenis_ticket = 'hardware' THEN d.nama_hardware
                        WHEN a.jenis_ticket = 'software' THEN e.nama_software
                        ELSE NULL
                    END as nama_item
                ");

        // FILTER
        $query->where('b.plant_id', $plantId);
        if (!$is_level4) $query->where('b.departemen_id', $departemenId);
        if ($request->jenis_ticket) $query->where('a.jenis_ticket', $request->jenis_ticket);
        if ($request->start_date && $request->end_date) $query->whereBetween('a.tgl_permintaan', [$request->start_date, $request->end_date]);
        if ($request->ticket_no) $query->where('a.ticket_no', $request->ticket_no);

        $tickets = $query
            ->orderBy('a.ticket_no')                     // 🔥 wajib pertama (DISTINCT ON)
            ->orderBy('a.tgl_permintaan', 'desc')
            ->get();

        if ($tickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        }
        $filtered = [];
        foreach ($tickets as $ticket) {

            // HANYA software & hardware
            if (!in_array($ticket->jenis_ticket, ['software', 'hardware'])) {
                continue;
            }

            $show_ticket  = false;
            $need_approve = false;

            // ===== LOGIC APPROVAL =====
            $maker_level = 0;
            $level1_users = json_decode($ticket->level1) ?: [];

            if ($ticket->user_create == $ticket->level4) $maker_level = 4;
            elseif ($ticket->user_create == $ticket->level3) $maker_level = 3;
            elseif ($ticket->user_create == $ticket->level2) $maker_level = 2;
            elseif (in_array($ticket->user_create, $level1_users)) $maker_level = 1;

            $ticket->maker_level = $maker_level;

            $approval_flow = [];
            if (!empty($ticket->level2)) $approval_flow[2] = $ticket->level2;
            if (!empty($ticket->level3)) $approval_flow[3] = $ticket->level3;
            if (!empty($ticket->level4)) $approval_flow[4] = $ticket->level4;

            $next_approval_level = null;

            foreach (array_keys($approval_flow) as $level) {
                if ($level > $maker_level) {
                    $next_approval_level = $level;
                    break;
                }
            }

            if ($next_approval_level) {
                $current_approver = $approval_flow[$next_approval_level];

                if ($username === $current_approver) {
                    $show_ticket  = true;
                    $status_field = 'status_level' . $next_approval_level;
                    $need_approve = empty($ticket->$status_field);
                }
            }
            $next_approval_level = null;

            foreach (array_keys($approval_flow) as $level) {
                if ($level > $maker_level) {
                    $next_approval_level = $level;
                    break;
                }
            }

            if ($next_approval_level) {
                $current_approver = $approval_flow[$next_approval_level];

                if ($username === $current_approver) {
                    $show_ticket  = true;
                    $status_field = 'status_level' . $next_approval_level;
                    $need_approve = empty($ticket->$status_field);
                }
            }

            if ($show_ticket) {
                $ticket->need_approve = $need_approve;
                $filtered[] = $ticket;
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data'    => array_values($filtered)
        ]);
    }
    public function approval_proses(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'ticket_no' => 'required|string',
                'status'    => 'required|in:approved,rejected',
                'remarks'   => 'nullable|string'
            ]);

            $username = Auth::user()->username;
            $splant_id = Auth::user()->plant_id;

            // Ambil ticket berdasarkan ticket_no
            $ticket = DB::table('tbl_tickets')->where('ticket_no', $request->ticket_no)->first();
            if (!$ticket) {
                return response()->json(['success' => false, 'message' => 'Ticket tidak ditemukan'], 404);
            }

            // Ambil user pembuat
            $user = DB::table('users')->where('username', $ticket->user_create)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User pembuat tidak ditemukan'], 404);
            }

            // Ambil hirarki berdasarkan plant_id & departemen_id user pembuat
            $hirarki = DB::table('user_hirarkis')
                ->where('plant_id', $user->plant_id)
                ->where('departemen_id', $user->departemen_id)
                ->where(function ($q) use ($ticket) {
                    $q->where('level2', $ticket->user_create)
                        ->orWhere('level3', $ticket->user_create)
                        ->orWhere('level4', $ticket->user_create)
                        ->orWhereJsonContains('level1', $ticket->user_create);
                })
                ->first();
            if (!$hirarki) {
    return response()->json([
        'success' => false,
        'message' => 'Hirarki user tidak ditemukan'
    ], 404);
}


            $updateFields   = [];
            $now            = now();
            $message        = "";
            $currentLevel   = null;

            if ($ticket->jenis_ticket == 'software' || $ticket->jenis_ticket == 'hardware') {
                $level1_users = json_decode($hirarki->level1, true) ?: [];


                $maker_level = 0;

                //cari pembuat tiket berada di level mana
                if ($ticket->user_create == $hirarki->level4) $maker_level           = 4;
                elseif ($ticket->user_create == $hirarki->level3) $maker_level       = 3;
                elseif ($ticket->user_create == $hirarki->level2) $maker_level       = 2;
                elseif (in_array($ticket->user_create, $level1_users)) $maker_level  = 1;


                // Level 2 approval
                if ($username == $ticket->approver_level2 && (is_null($ticket->status_level2) || $ticket->status_level2 === '')) {
                    $currentLevel = 2;
                    if ($maker_level     != 1) {
                        return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak approve level 2'], 403);
                    }
                    $updateFields = [
                        'status_level2' => $request->status == 'approved' ? TRUE : FALSE,
                        'remarks2' => $request->remarks ?? null,
                        'date_level2' => $now
                    ];
                    if ($request->status == 'approved') {
                        $message = "Ticket $ticket->ticket_no membutuhkan approval berikutnya.";
                        NotificationHelper::send($ticket->ticket_no, $ticket->approver_level3, $splant_id, $message);
                    }
                }
                // Level 3 approval
                elseif ($username == $ticket->approver_level3 && (is_null($ticket->status_level3) || $ticket->status_level3 === '')) {
                    $currentLevel = 3;
                    if (($maker_level == 1 && $ticket->status_level2 != TRUE) || $maker_level > 2) {
                        return response()->json(['success' => false, 'message' => 'Level sebelumnya belum approve'], 400);
                    }
                    $updateFields = [
                        'status_level3' => $request->status == 'approved' ? TRUE : FALSE,
                        'remarks3' => $request->remarks ?? null,
                        'date_level3' => $now
                    ];
                    if ($request->status == 'approved') {
                        $message = "Ticket $ticket->ticket_no menunggu approval Level 4.";
                        NotificationHelper::send($ticket->ticket_no, $ticket->approver_level4, $splant_id, $message);
                    }
                }
                // Level 4 approval
                elseif ($username == $ticket->approver_level4 && (is_null($ticket->status_level4) || $ticket->status_level4 === '')) {
                    $currentLevel = 4;
                    if (($maker_level == 1 && ($ticket->status_level2 != TRUE || ($ticket->approver_level3 && $ticket->status_level3 != TRUE))) ||
                        ($maker_level == 2 && ($ticket->approver_level3 && $ticket->status_level3 != TRUE))
                    ) {
                        return response()->json(['success' => false, 'message' => 'Level sebelumnya belum approve'], 400);
                    }
                    $updateFields = [
                        'status_level4' => $request->status == 'approved' ? TRUE : FALSE,
                        'remarks4' => $request->remarks ?? null,
                        'date_level4' => $now
                    ];
                    if ($request->status == 'approved') {
                        $message = "Ticket Anda ($ticket->ticket_no) telah FULL APPROVED.";
                        NotificationHelper::send($ticket->ticket_no, $ticket->user_create, $splant_id, $message);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak approve ticket ini'], 403);
                }

                if ($request->status == "rejected") {
                    $levelMsg = $currentLevel ? "pada Level $currentLevel" : "";
                    $message = "Ticket Anda ($ticket->ticket_no) DITOLAK oleh $username $levelMsg.";
                    NotificationHelper::send($ticket->ticket_no, $ticket->user_create, $splant_id, $message);
                }

                if ($username == $ticket->approver_level4) {
                    if ($request->status == 'approved') {
                        $statusApproval = 'approved';
                    } elseif ($request->status == 'rejected') {
                        $statusApproval = 'rejected';
                    }
                } else {

                    // Level lain: HANYA BOLEH ubah jika rejected
                    if ($request->status === 'rejected') {
                        $statusApproval = 'rejected';
                    } else {
                        // approved / lainnya tidak mengubah status
                        $statusApproval = $ticket->status_approval ?? 'waiting';
                    }
                }

                $updateFields['status_approval'] = $statusApproval;
            }

            // Update ke database
            DB::table('tbl_tickets')->where('ticket_no', $ticket->ticket_no)->update($updateFields);
            ActivityLogger::log(
                $request->status == 'approved' ? 'approve' : 'reject',
                'Ticket',
                'Primary: ' . $ticket->ticket_no
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status == 'approved'
                    ? 'Ticket berhasil di APPROVE!'
                    : 'Ticket berhasil ditolak.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat ticket: ' . $e->getMessage(),
            ], 500);
        }
    }

    //track ticket
    public function track_ticket()
    {
        return view('ticketings.track_ticket');
    }
    public function data_track(Request $request)
    {
        $user   = Auth::user();
        $deptId = $user->departemen_id;

        $query = DB::table('tbl_tickets as a')
            ->leftJoin('users as b', 'a.user_create', '=', 'b.username')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('hardwares as d', 'a.item_ticket', '=', 'd.id_hardware')
            ->leftJoin('softwares as e', 'a.item_ticket', '=', 'e.id_software')
            ->select(
                'a.*',
                'b.nama_lengkap as nama_lengkap',
                'c.nama_departemen as nama_departemen',
                'd.nama_hardware as nama_hardware',
                'e.nama_software as nama_software',
            )
            ->where('b.departemen_id', $deptId);;
        if ($request->start_date && $request->end_date) $query->whereBetween('tgl_permintaan', [$request->start_date, $request->end_date]);
        if ($request->jenis_ticket) $query->where('jenis_ticket', $request->jenis_ticket);
        if ($request->status_approval) $query->where('status_approval', $request->status_approval);
        if ($request->status_problem) $query->where('status_problem', $request->status_problem);

        $ticketings = $query->orderBy('tgl_permintaan', 'desc')->get();
        // dd($query->toSql(), $query->getBindings());
        if ($ticketings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $ticketings
        ]);
    }

    //queue ticket
    public function queue_ticket()
    {
        return view('ticketings.queue_ticket');
    }
    public function data_queue(Request $request)
    {
        $query = Ticketing::from('tbl_tickets as a')
            ->leftJoin('users as b', 'a.user_create', '=', 'b.username')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('hardwares as d', 'a.item_ticket', '=', 'd.id_hardware')
            ->leftJoin('softwares as e', 'a.item_ticket', '=', 'e.id_software')
            ->select(
                'a.*',
                'b.nama_lengkap as nama_lengkap',
                'c.nama_departemen as nama_departemen',
                'd.nama_hardware as nama_hardware',
                'e.nama_software as nama_software',
            );
        if ($request->start_date && $request->end_date) $query->whereBetween('tgl_permintaan', [$request->start_date, $request->end_date]);
        if ($request->ticket_no) $query->where('ticket_no', $request->ticket_no);
        if ($request->jenis_ticket) $query->where('jenis_ticket', $request->jenis_ticket);
        if ($request->status_approval) $query->where('status_approval', $request->status_approval);
        if ($request->departemen) $query->where('b.departemen_id', $request->departemen);
        $ticketings = $query->orderBy('tgl_permintaan', 'desc')->get();
        if ($ticketings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $ticketings
        ]);
    }

    //incoming software
    public function incoming_software()
    {
        return view('ticketings.incoming_software');
    }
    public function data_incoming_software(Request $request)
    {
        $query = Ticketing::from('tbl_tickets as a')
            ->leftJoin('users as b', 'a.user_create', '=', 'b.username')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('softwares as d', 'a.item_ticket', '=', 'd.id_software')
            ->leftJoin('plants as f', 'b.plant_id', '=', 'f.id_plant')
            ->where('a.status_level4', TRUE)
            ->select(
                'a.*',
                'b.nama_lengkap as nama_lengkap',
                'c.nama_departemen as nama_departemen',
                'd.nama_software as nama_software',
                'f.nama_plant as nama_plant'
            );

        if ($request->start_date && $request->end_date) $query->whereBetween('tgl_permintaan', [$request->start_date, $request->end_date]);
        if ($request->jenis_ticket) $query->where('jenis_ticket', $request->jenis_ticket);
        if ($request->status_problem) $query->where('status_problem', $request->status_problem);
        if ($request->kategori_klaim) $query->where('kategori_klaim', $request->kategori_klaim);

        $ticketings = $query->orderBy('tgl_permintaan', 'desc')->get();
        if ($ticketings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $ticketings
        ]);
    }
    public function sw_start_proses(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'ticket_no'  => 'required|string',
            'start_time' => 'required|string'
        ]);

        // Ambil ticket
        $ticket = Ticketing::from('tbl_tickets as t')
            ->leftJoin('users as u', 't.user_create', '=', 'u.username')
            ->where('t.ticket_no', $request->ticket_no)
            ->select('t.*', 'u.plant_id')
            ->first();


        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket tidak ditemukan.'], 404);
        }

        // Update
        $ticket->update([
            'time_start'     => $request->start_time,
            'it_start'       => $user->username,
            'status_problem' => 'on_progress'
        ]);

        // Kirim notifikasi
        $message = "Ticket Anda ($ticket->ticket_no) sedang diproses oleh tim IT.";
        NotificationHelper::send($ticket->ticket_no, $ticket->user_create, $ticket->plant_id, $message);

        return response()->json(['success' => true, 'message' => 'Ticket telah dimulai.']);
    }
    public function sw_finish_proses(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'ticket_no'    => 'required|string',
            'finish_time'  => 'required|string'
        ]);

        // Ambil ticket
        $ticket = Ticketing::from('tbl_tickets as t')
            ->leftJoin('users as u', 't.user_create', '=', 'u.username')
            ->where('t.ticket_no', $request->ticket_no)
            ->select('t.*', 'u.plant_id')
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket tidak ditemukan.'], 404);
        }

        // Update ticket
        $ticket->update([
            'time_finish'      => $request->finish_time,
            'jenis_problem'    => $request->jenis_problem,
            'it_finish'        => $user->username,
            'status_problem'   => 'closed',
        ]);

        // Kirim notifikasi selesai
        $message = "Ticket Anda ($ticket->ticket_no) telah selesai dikerjakan.";
        NotificationHelper::send($ticket->ticket_no, $ticket->user_create, $ticket->plant_id, $message);

        return response()->json(['success' => true, 'message' => 'Ticket telah selesai.']);
    }


    //incoming hardware
    public function incoming_hardware()
    {
        return view('ticketings.incoming_hardware');
    }
    public function data_incoming_hardware(Request $request)
    {
        $query = Ticketing::from('tbl_tickets as a')
            ->leftJoin('users as b', 'a.user_create', '=', 'b.username')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('hardwares as d', 'a.item_ticket', '=', 'd.id_hardware')
            ->leftJoin('plants as f', 'b.plant_id', '=', 'f.id_plant')
            ->select(
                'a.*',
                'b.nama_lengkap as nama_lengkap',
                'c.nama_departemen as nama_departemen',
                'd.nama_hardware as nama_hardware',
                'f.nama_plant as nama_plant'
            );
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('tgl_permintaan', [$request->start_date, $request->end_date]);
        }
        if ($request->jenis_ticket) {
            $query->where('jenis_ticket', $request->jenis_ticket);
        }
        if ($request->ticket_no) {
            $query->where('ticket_no', $request->ticket_no);
        }
        if ($request->departemen) {
            $query->where('b.departemen_id', $request->departemen);
        }
        // dd($query->toSql(), $query->getBindings());
        // dd($query->get());  
        $ticketings = $query->orderBy('tgl_permintaan', 'desc')->get();
        if ($ticketings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $ticketings
        ]);
    }
    public function hw_start_proses(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'ticket_no'  => 'required|string',
            'start_time' => 'required|string'
        ]);

        // Ambil ticket
        $ticket = Ticketing::from('tbl_tickets as t')
            ->leftJoin('users as u', 't.user_create', '=', 'u.username')
            ->where('t.ticket_no', $request->ticket_no)
            ->select('t.*', 'u.plant_id')
            ->first();


        if (!$ticket) return response()->json(['success' => false, 'message' => 'Ticket tidak ditemukan.'], 404);

        // Update ticket
        $ticket->update([
            'time_start'     => $request->start_time,
            'it_start'       => $user->username,
            'status_problem' => 'on_progress'
        ]);

        // Kirim notifikasi
        $message = "Ticket Anda ($ticket->ticket_no) sedang diproses oleh tim IT.";
        NotificationHelper::send(
            $ticket->ticket_no,
            $ticket->user_create,
            $ticket->plant_id,
            $message
        );

        return response()->json(['success' => true, 'message' => 'Ticket telah dimulai.']);
    }
    public function hw_finish_proses(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'ticket_no'    => 'required|string',
            'finish_time'  => 'required|string'
        ]);
        // Ambil ticket
        $ticket = Ticketing::from('tbl_tickets as t')
            ->leftJoin('users as u', 't.user_create', '=', 'u.username')
            ->where('t.ticket_no', $request->ticket_no)
            ->select('t.*', 'u.plant_id')
            ->first();


        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket tidak ditemukan.'], 404);
        }

        // Update ticket
        $ticket->update([
            'time_finish'      => $request->finish_time,
            'jenis_problem'    => $request->jenis_problem,
            'counter_measure'  => $request->counter_measure,
            'next_plan'        => $request->next_plan,
            'jenis_pengecekan' => $request->jenis_pengecekan,
            'it_finish'        => $user->username,
            'status_problem'   => 'closed',
        ]);

        // Kirim notifikasi selesai
        $message = "Ticket Anda ($ticket->ticket_no) telah selesai dikerjakan.";
        NotificationHelper::send(
            $ticket->ticket_no,
            $ticket->user_create,
            $ticket->plant_id,
            $message
        );

        return response()->json(['success' => true, 'message' => 'Ticket telah selesai.']);
    }

    //user confirm
    public function user_confirm_hardware()
    {
        return view('ticketings.user_confirm_hardware');
    }
    public function data_user_confirm_hardware()
    {
        $user = Auth::user();
        $username = $user->username;

        $tickets = Ticketing::from('tbl_tickets as a')
            ->select(
                'a.*',
                'b.nama_lengkap',
                'c.nama_departemen',
                'd.nama_hardware as nama_hardware'
            )
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->leftJoin('hardwares as d', 'a.item_ticket', '=', 'd.id_hardware')
            ->where('a.user_create', $username)
            ->where('a.status_problem', 'closed')
            ->whereNull('a.usercreate_confirm')
            ->orderBy('a.tgl_permintaan', 'desc')
            ->get();

        $filtered = [];
        foreach ($tickets as $ticket) {
            // karena sudah whereNull di query, ini aman
            if ($ticket->usercreate_confirm === null || $ticket->usercreate_confirm === '') {
                $ticket->need_approve = true;
            } else {
                $ticket->need_approve = false;
            }

            $filtered[] = $ticket;
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $filtered
        ]);
    }
    public function proses_user_confirm_hardware(Request $request)
    {
        $request->validate([
            'ticket_no' => 'required',
            'status'    => 'required|in:ok,ng'
        ]);

        $user = Auth::user();
        $username = $user->username;

        $ticket = Ticketing::where('ticket_no', $request->ticket_no)
            ->where('user_create', $username)
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket tidak ditemukan'
            ], 404);
        }

        // cegah konfirmasi 2x
        if (!is_null($ticket->usercreate_confirm)) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket sudah dikonfirmasi'
            ], 400);
        }

        // update data (PAKAI UPDATE SAJA)
        $ticket->update([
            'usercreate_confirm'      => ($request->status === 'ok'),
            'date_usercreate_confirm' => now(),
            'status_akhir_user'       => $request->status,
            'remarks'                 => $request->remarks
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi hardware berhasil disimpan'
        ]);
    }
}
