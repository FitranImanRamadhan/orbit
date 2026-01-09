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
use Mpdf\Mpdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;


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
                'tglPermintaan' => 'required|date_format:Y-m-d',
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
                return response()->json(['message' => 'Hirarki user tidak ditemukan untuk plant dan departemen']);
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
            if (strtolower($request->jenisTicket) == 'software') {

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
            } elseif (strtolower($request->jenisTicket) === 'hardware') {
                // hanya pakai level 3
                $approvers['approver_level3'] = $hirarki->level3;
                // level 2 & 4 BIARKAN NULL (jangan diisi apa pun)
            }

            // 1. Generate no ticket
            $plantId = Auth::user()->plant_id;
            $plant = DB::table('plants')->where('id_plant', $plantId)->first();
            $label_plant = $plant->label ?? '-';
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
                'tgl_permintaan'     => $request->tglPermintaan . ' ' . now()->format('H:i:s'),
                'kategori_klaim'     => $request->kategoriKlaim,
                'deskripsi'          => $request->deskripsi,
                'user_create'        => $username,
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
                        "Ticket ($ticket_no) otomatis disetujui karena berada di level tertinggi approval."
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
            ->where('status_approval', 'waiting')
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
            $show_ticket = false;
            $need_approve = false;
            // Tentukan maker level
            $maker_level  = 0;
            $level1_users = json_decode($ticket->level1) ?: [];
            if ($ticket->user_create == $ticket->level4) $maker_level = 4;
            elseif ($ticket->user_create == $ticket->level3) $maker_level = 3;
            elseif ($ticket->user_create == $ticket->level2) $maker_level = 2;
            elseif (in_array($ticket->user_create, $level1_users)) $maker_level = 1;
            $ticket->maker_level = $maker_level;

            // APPROVAL FLOW manual
            $approvalFlow = [];
            if (!empty($ticket->approver_level2)) {
                $user2 = User::getDetailByUsername($ticket->approver_level2);
                $approvalFlow[2] = ['username' => $ticket->approver_level2, 'status' => $ticket->status_level2, 'nama_lengkap' => $user2->nama_lengkap ?? $ticket->approver_level2];
            }
            if (!empty($ticket->approver_level3)) {
                $user3 = User::getDetailByUsername($ticket->approver_level3);
                $approvalFlow[3] = ['username' => $ticket->approver_level3,'status' => $ticket->status_level3,'nama_lengkap' => $user3->nama_lengkap ?? $ticket->approver_level3
                ];
            }
            if (!empty($ticket->approver_level4)) {
                $user4 = User::getDetailByUsername($ticket->approver_level4);
                $approvalFlow[4] = ['username' => $ticket->approver_level4, 'status' => $ticket->status_level4, 'nama_lengkap' => $user4->nama_lengkap ?? $ticket->approver_level4];
            }

            if ($ticket->jenis_ticket == 'software') {

                // NEXT APPROVER manual
                // LEVEL 2
                if (isset($approvalFlow[2]) && $maker_level < 2 && is_null($approvalFlow[2]['status']) && $username === $approvalFlow[2]['username']) {
                    $show_ticket = true;
                    $need_approve = true;
                }

                // LEVEL 3
                elseif (isset($approvalFlow[3]) && $maker_level < 3 && is_null($approvalFlow[3]['status']) && $username === $approvalFlow[3]['username']) {
                    // cek level sebelumnya harus approve atau NULL
                    $level2_ok = !isset($approvalFlow[2]) || $approvalFlow[2]['status'] === true || is_null($approvalFlow[2]['status']);
                    if ($level2_ok) {
                        $show_ticket = true;
                        $need_approve = true;
                    }
                }

                // LEVEL 4
                elseif (isset($approvalFlow[4]) && $maker_level < 4 && is_null($approvalFlow[4]['status']) && $username === $approvalFlow[4]['username']) {
                    // cek level sebelumnya harus approve atau NULL
                    $level2_ok = !isset($approvalFlow[2]) || $approvalFlow[2]['status'] === true || is_null($approvalFlow[2]['status']);
                    $level3_ok = !isset($approvalFlow[3]) || $approvalFlow[3]['status'] === true || is_null($approvalFlow[3]['status']);

                    if ($level2_ok && $level3_ok) {
                        $show_ticket = true;
                        $need_approve = true;
                    }
                }
            } elseif ($ticket->jenis_ticket === 'hardware') {
                if (isset($approvalFlow[3]) && $maker_level < 3 && is_null($approvalFlow[3]['status']) && $username === $approvalFlow[3]['username']) {
                    $show_ticket = true;
                    $need_approve = true;
                }
            }

            if ($show_ticket) {
                $ticket->need_approve = $need_approve;
                $ticket->approvalFlow = $approvalFlow;
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

            $pengguna_login = Auth::user()->username;
            $plant_id_login = Auth::user()->plant_id;

            // Ambil tiket
            $tiket = DB::table('tbl_tickets')->where('ticket_no', $request->ticket_no)->first();
            if (!$tiket) {
                return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan'], 404);
            }

            // Ambil user pembuat tiket
            $user_pembuat = DB::table('users')->where('username', $tiket->user_create)->first();
            if (!$user_pembuat) {
                return response()->json(['success' => false, 'message' => 'User pembuat tidak ditemukan'], 404);
            }

            // Ambil hirarki user pembuat
            $hirarki_user = DB::table('user_hirarkis')
                ->where('plant_id', $user_pembuat->plant_id)
                ->where('departemen_id', $user_pembuat->departemen_id)
                ->where(function ($q) use ($tiket) {
                    $q->where('level2', $tiket->user_create)
                        ->orWhere('level3', $tiket->user_create)
                        ->orWhere('level4', $tiket->user_create)
                        ->orWhereJsonContains('level1', $tiket->user_create);
                })
                ->first();

            $waktu_sekarang = now();
            $update_data = [];

            // ================= LOGIC APPROVAL DINAMIS =================
            if ($tiket->jenis_ticket == 'software') {
                $level1_users = json_decode($hirarki_user->level1, true) ?: [];

                $update_data = [];

                // ==== LEVEL 2 ====
                if (!empty($tiket->approver_level2) && $pengguna_login == $tiket->approver_level2) {
                    if (is_null($tiket->status_level2) || $tiket->status_level2 === '') {
                        $update_data['status_level2'] = $request->status == 'approved' ? true : ($request->status == 'rejected' ? false : null);
                        $update_data['remarks2'] = $request->remarks ?? null;
                        $update_data['date_level2'] = $waktu_sekarang;
                        $update_data['status_approval'] = $request->status == 'approved' ? 'waiting' : 'rejected';
                        $update_data['status_problem'] = $request->status == 'approved' ? '' : 'canceled';

                        $penerima_selanjutnya = $tiket->approver_level3 ?? $tiket->approver_level4 ?? $tiket->user_create;
                        if ($request->status == 'approved') {
                            $pesan = "Tiket $tiket->ticket_no membutuhkan approval berikutnya.";
                            NotificationHelper::send($tiket->ticket_no, $penerima_selanjutnya, $plant_id_login, $pesan);

                            $pesan_user = "Tiket $tiket->ticket_no telah disetujui pada tahap 1.";
                            NotificationHelper::send($tiket->ticket_no, $tiket->user_create, $plant_id_login, $pesan_user);
                        } else {
                            $pesan = "Tiket $tiket->ticket_no anda telah ditolak pada tahap 1.";
                            NotificationHelper::send($tiket->ticket_no, $tiket->user_create, $plant_id_login, $pesan);
                        }
                    } else {
                        return response()->json(['success' => false, 'message' => 'Tahap 2 sudah diapprove'], 403);
                    }
                }

                // ==== LEVEL 3 ====
                if (!empty($tiket->approver_level3) && $pengguna_login == $tiket->approver_level3) {
                    // Cek level sebelumnya jika ada
                    if (!empty($tiket->approver_level2) && $tiket->status_level2 !== TRUE) {
                        return response()->json(['success' => false, 'message' => 'Level sebelumnya belum approve'], 403);
                    }

                    if (is_null($tiket->status_level3) || $tiket->status_level3 === '') {
                        $update_data['status_level3'] = $request->status == 'approved' ? true : ($request->status == 'rejected' ? false : null);
                        $update_data['remarks3'] = $request->remarks ?? null;
                        $update_data['date_level3'] = $waktu_sekarang;
                        $update_data['status_approval'] = $request->status == 'approved' ? 'waiting' : 'rejected';
                        $update_data['status_problem'] = $request->status == 'approved' ? null : 'canceled';

                        $penerima_selanjutnya = $tiket->approver_level4 ?? $tiket->user_create;
                        if ($request->status == 'approved') {
                            $pesan = "Tiket $tiket->ticket_no membutuhkan approval berikutnya.";
                            NotificationHelper::send($tiket->ticket_no, $penerima_selanjutnya, $plant_id_login, $pesan);

                            $pesan_user = "Tiket $tiket->ticket_no telah disetujui pada tahap 2.";
                            NotificationHelper::send($tiket->ticket_no, $tiket->user_create, $plant_id_login, $pesan_user);
                        } else {
                            $pesan = "Tiket $tiket->ticket_no anda telah ditolak pada tahap 2.";
                            NotificationHelper::send($tiket->ticket_no, $tiket->user_create, $plant_id_login, $pesan);
                        }
                    } else {
                        return response()->json(['success' => false, 'message' => 'Tahap 3 sudah diapprove'], 403);
                    }
                }

                // ==== LEVEL 4 ====
                if (!empty($tiket->approver_level4) && $pengguna_login == $tiket->approver_level4) {
                    // Cek level sebelumnya jika ada
                    if ((!empty($tiket->approver_level2) && $tiket->status_level2 !== TRUE) ||
                        (!empty($tiket->approver_level3) && $tiket->status_level3 !== TRUE)
                    ) {
                        return response()->json(['success' => false, 'message' => 'Tahap sebelumnya belum approve'], 403);
                    }

                    if (is_null($tiket->status_level4) || $tiket->status_level4 === '') {
                        $update_data['status_level4'] = $request->status == 'approved' ? true : ($request->status == 'rejected' ? false : null);
                        $update_data['remarks4'] = $request->remarks ?? null;
                        $update_data['date_level4'] = $waktu_sekarang;
                        $update_data['status_approval'] = $request->status == 'approved' ? 'approved' : 'rejected';
                        $update_data['status_problem'] = $request->status == 'approved' ? 'open' : 'canceled';

                        $pesan = $request->status == 'approved'
                            ? "Tiket anda($tiket->ticket_no) telah FULL APPROVED."
                            : "Tiket anda ($tiket->ticket_no) Ditolah pada tahap 4.";
                        NotificationHelper::send($tiket->ticket_no, $tiket->user_create, $plant_id_login, $pesan);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Tahap 4 sudah diapprove'], 403);
                    }
                }

                if (empty($update_data)) {
                    return response()->json(['success' => false, 'message' => 'Tidak ada level approval yang valid atau user tidak memiliki hak'], 403);
                }

                // Update database
                DB::table('tbl_tickets')->where('ticket_no', $tiket->ticket_no)->update($update_data);
            } elseif ($tiket->jenis_ticket == 'hardware') {

                $update_data = [];

                // ==== LEVEL 3 SAJA (FINAL) ====
                if (!empty($tiket->approver_level3) && $pengguna_login == $tiket->approver_level3) {

                    if (is_null($tiket->status_level3) || $tiket->status_level3 === '') {

                        $update_data['status_level3'] = $request->status == 'approved' ? true : false;
                        $update_data['remarks3'] = $request->remarks ?? null;
                        $update_data['date_level3'] = $waktu_sekarang;
                        $update_data['status_approval'] = $request->status == 'approved' ? 'approved' : 'rejected';
                        $update_data['status_problem'] = $request->status == 'approved' ? 'open' : 'canceled';

                        $pesan = $request->status == 'approved'
                            ? "Tiket anda ($tiket->ticket_no) telah DISETUJUI."
                            : "Tiket anda ($tiket->ticket_no) DITOLAK.";
                        NotificationHelper::send($tiket->ticket_no,  $tiket->user_create, $plant_id_login, $pesan);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Level 3 sudah diapprove'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'User tidak memiliki hak approval level 3'
                    ], 403);
                }

                // Update database
                DB::table('tbl_tickets')->where('ticket_no', $tiket->ticket_no)->update($update_data);
            }
            // Log aktivitas
            ActivityLogger::log($request->status == 'approved' ? 'approve' : 'reject', 'Tiket', 'Primary: ' . $tiket->ticket_no);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status == 'approved'
                    ? 'Tiket berhasil di APPROVE!'
                    : 'Tiket berhasil ditolak.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses tiket: ' . $e->getMessage(),
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
            ->where('b.departemen_id', $deptId)
            ->where('a.user_create', $user->username);
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
            ->whereNotNull('a.status_problem')               // status_problem bukan null
            ->where('a.status_problem', '!=', 'canceled')
            ->where('a.status_approval', '=', 'approved')   // status_problem bukan 'canceled'
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

        $ticketings = $query->orderByRaw("CASE WHEN a.status_problem = 'open' THEN 0 ELSE 1 END")
            ->orderBy('tgl_permintaan', 'desc')
            ->get();

        //foreach hanya untuk mengmbail detial nama lengkap saja
        foreach ($ticketings as $ticket) {
            if (!empty($ticket->approver_level2)) {
                $user2 = User::getDetailByUsername($ticket->approver_level2);
                $ticket->nama_lengkap2 = $user2->nama_lengkap ?? $ticket->approver_level2;
            }
            if (!empty($ticket->approver_level3)) {
                $user3 = User::getDetailByUsername($ticket->approver_level3);
                $ticket->nama_lengkap3 = $user3->nama_lengkap ?? $ticket->approver_level3;
            }
            if (!empty($ticket->approver_level4)) {
                $user4 = User::getDetailByUsername($ticket->approver_level4);
                $ticket->nama_lengkap4 = $user4->nama_lengkap ?? $ticket->approver_level4;
            }
        }

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
        $message = "Ticket ($ticket->ticket_no) sedang diproses oleh tim IT.";
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
        $message = "Ticket ($ticket->ticket_no) telah selesai dikerjakan.";
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
            ->where('jenis_ticket', $request->jenis_ticket)  // status_problem bukan 'canceled'
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
        if ($request->ticket_no) {
            $query->where('ticket_no', $request->ticket_no);
        }
        if ($request->departemen) {
            $query->where('b.departemen_id', $request->departemen);
        }
        // dd($query->toSql(), $query->getBindings());
        // dd($query->get());  
        $ticketings = $query->orderByRaw("CASE WHEN a.status_problem = 'open' THEN 0 ELSE 1 END")
            ->orderBy('tgl_permintaan', 'desc')
            ->get();

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
        $message = "Ticket ($ticket->ticket_no) sedang diproses oleh tim IT.";
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
        $message = "Ticket ($ticket->ticket_no) telah selesai dikerjakan.";
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

    public function incoming_software_pdf(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'mode' => 'nullable|in:preview,download'
        ]);

        $data = Ticketing::from('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->join('users as c', 'c.username', '=', 'a.it_finish')
            ->leftJoin('softwares as d', 'a.item_ticket', '=', 'd.id_software')
            ->leftJoin('plants as e', 'e.id_plant', '=', 'b.plant_id')          // plant pemohon
            ->leftJoin('departemens as f', 'f.id_departemen', '=', 'b.departemen_id') // departemen pemohon
            ->leftJoin('positions as g', 'g.id_position', '=', 'b.position_id')       // posisi pemohon
            ->leftJoin('plants as h', 'h.id_plant', '=', 'c.plant_id')          // plant IT
            ->leftJoin('departemens as i', 'i.id_departemen', '=', 'c.departemen_id') // departemen IT
            ->leftJoin('positions as j', 'j.id_position', '=', 'c.position_id')      // posisi IT
            ->select(
                'a.*',
                'b.username as user_create',
                'b.plant_id as plant_id_user_create',
                'e.nama_plant as plant_name_user_create',
                'e.label',
                'b.departemen_id as dept_id_user_create',
                'f.nama_departemen as dept_name_user_create',
                'g.nama_position as position_user_create',
                'b.nama_lengkap as nama_pemohon',

                'c.username as it_finish',
                'c.plant_id as plant_id_it_finish',
                'h.nama_plant as plant_name_it_finish',
                'c.departemen_id as dept_id_it_finish',
                'i.nama_departemen as dept_name_it_finish',
                'j.nama_position as position_it_finish',
                'c.nama_lengkap as nama_it',

                'd.nama_software'
            )
            ->where('a.id', $request->id)
            ->where('a.jenis_ticket', 'software')
            ->firstOrFail();


        // ===================== PEMOHON ========================
        $hirarkiPemohon = DB::table('user_hirarkis')
            ->where('plant_id', $data->plant_id_user_create)
            ->where('departemen_id', $data->dept_id_user_create)
            ->where(function ($q) use ($data) {
                $q->orWhereJsonContains('level1', $data->user_create)
                    ->orWhere('level2', $data->user_create)
                    ->orWhere('level3', $data->user_create);
            })
            ->first();

        $pemohon = [
            'level2' => User::getDetailByUsername($hirarkiPemohon->level2 ?? null), //getdetailbyusername isinya ada namalengkap, nama departemen dan lain lain
            'level3' => User::getDetailByUsername($hirarkiPemohon->level3 ?? null),
            'level4' => User::getDetailByUsername($hirarkiPemohon->level4 ?? null),
        ];


        // ================= LOGIKA FINAL PEMOHON =================
        $finalPemohon = [
            'diperiksa' => null,
            'diketahui' => null,
            'diperiksa_level' => null,
            'diketahui_level' => null,
        ];

        if (empty($hirarkiPemohon->level2)) {
            $finalPemohon['diperiksa'] = $pemohon['level3'];
            $finalPemohon['diketahui'] = $pemohon['level4'];
            $finalPemohon['diperiksa_level'] = 'level3';
            $finalPemohon['diketahui_level'] = 'level4';
        } elseif (empty($hirarkiPemohon->level3)) {
            $finalPemohon['diperiksa'] = $pemohon['level2'];
            $finalPemohon['diketahui'] = $pemohon['level4'];
            $finalPemohon['diperiksa_level'] = 'level2';
            $finalPemohon['diketahui_level'] = 'level4';
        } else {
            $finalPemohon['diperiksa'] = $pemohon['level2'];
            $finalPemohon['diketahui'] = $pemohon['level3'];
            $finalPemohon['diperiksa_level'] = 'level2';
            $finalPemohon['diketahui_level'] = 'level3';
        }

        // ======================= IT ============================
        $hirarkiIt = DB::table('user_hirarkis')
            ->where('plant_id', $data->plant_id_it_finish)
            ->where('departemen_id', $data->dept_id_it_finish)
            ->where(function ($q) use ($data) {
                $q->orWhereJsonContains('level1', $data->it_finish)
                    ->orWhere('level2', $data->it_finish)
                    ->orWhere('level3', $data->it_finish);
            })
            ->first();

        $it = [
            'level2' => User::getDetailByUsername($hirarkiIt->level2 ?? null),
            'level3' => User::getDetailByUsername($hirarkiIt->level3 ?? null),
        ];

        // IT: diperiksa = level2, diketahui = level3
        $finalIt = [
            'diperiksa' => $it['level2'],
            'diketahui' => $it['level3'],
        ];

        // =====================================================
        // ======================= QR ============================
        // =====================================================
        $makeQr = function ($text) {
            if (!$text) {
                return null;
            }
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($text)
                ->encoding(new Encoding('UTF-8'))
                ->size(150)
                ->margin(0)
                ->build();

            return 'data:image/png;base64,' . base64_encode($result->getString());
        };

        // QR DIPERIKSA (PEMOHON)
        $finalPemohon['diperiksa_qr'] = null;

        if ($finalPemohon['diperiksa']) {
            if (
                ($finalPemohon['diperiksa_level'] === 'level2' && $data->status_level2 == TRUE) ||
                ($finalPemohon['diperiksa_level'] === 'level3' && $data->status_level3 == TRUE) ||
                ($finalPemohon['diperiksa_level'] === 'level4' && $data->status_level4 == TRUE)
            ) {
                $finalPemohon['diperiksa_qr'] = $makeQr(
                    "Nama: {$finalPemohon['diperiksa']->nama_lengkap}\n" .
                    "Posisi: {$finalPemohon['diperiksa']->nama_position}\n" .
                    "Departemen: {$finalPemohon['diperiksa']->nama_departemen}\n" .
                    "Plant: {$finalPemohon['diperiksa']->nama_plant}"
                );
            }
        }


        // QR DIKETAHUI (PEMOHON)
        $finalPemohon['diketahui_qr'] = null;
        if ($finalPemohon['diketahui']) {
            if (
                ($finalPemohon['diketahui_level'] === 'level2' && $data->status_level2) ||
                ($finalPemohon['diketahui_level'] === 'level3' && $data->status_level3) ||
                ($finalPemohon['diketahui_level'] === 'level4' && $data->status_level4)
            ) {
                $finalPemohon['diketahui_qr'] = $makeQr(
                    "Nama: {$finalPemohon['diketahui']->nama_lengkap}\n" .
                        "Posisi: {$finalPemohon['diketahui']->nama_position}\n" .
                        "Dept: {$finalPemohon['diketahui']->nama_departemen}\n" .
                        "Plant: {$finalPemohon['diketahui']->nama_plant}"
                );
            }
        }


        // IT
        $finalIt['diperiksa_qr'] = $finalIt['diperiksa']
            ? $makeQr(
                "Nama: {$finalIt['diperiksa']->nama_lengkap}\n" .
                    "Posisi: {$finalIt['diperiksa']->nama_position}\n" .
                    "Dept: {$finalIt['diperiksa']->nama_departemen}\n" .
                    "Plant: {$finalIt['diperiksa']->nama_plant}"
            ) : null;

        $finalIt['diketahui_qr'] = $finalIt['diketahui']
            ? $makeQr(
                "Nama: {$finalIt['diketahui']->nama_lengkap}\n" .
                    "Posisi: {$finalIt['diketahui']->nama_position}\n" .
                    "Dept: {$finalIt['diketahui']->nama_departemen}\n" .
                    "Plant: {$finalIt['diketahui']->nama_plant}"
            ) : null;


        // PEMOHON CREATE (REQUESTER)
        $qrPemohon = $makeQr(
            "Nama: {$data->nama_pemohon}\n" .
                "Posisi: {$data->position_user_create}\n" .
                "Dept: {$data->dept_name_user_create}\n" .
                "Plant: {$data->plant_name_user_create}"
        );

        // IT FINISH
        $qrItFinish = $makeQr(
            "Nama: {$data->nama_it}\n" .
                "Posisi: {$data->position_it_finish}\n" .
                "Dept: {$data->dept_name_it_finish}\n" .
                "Plant: {$data->plant_name_it_finish}"
        );


        // ====================== RENDER =========================
        $html = view('ticketings.incoming_software_pdf', [
            'data'         => $data,
            'finalPemohon' => $finalPemohon,
            'finalIt'      => $finalIt,
            'qrPemohon'    => $qrPemohon,
            'qrItFinish'   => $qrItFinish,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 5,
            'margin_bottom' => 45, // RUANG FOOTER
            'margin_left'   => 5,
            'margin_right'  => 5,
            'image_backend' => 'GD',
        ]);
        $mpdf->WriteHTML($html);
        $outputMode = $request->mode === 'download' ? 'D' : 'I';

        return response(
            $mpdf->Output('incoming-software.pdf', $outputMode),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
}
