<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ReportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\QrTempCleaner;
use App\Helpers\ActivityLogger;
use App\Exports\TicketReportExport;
use App\Helpers\NotificationHelper;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportController extends Controller
{
    //report hardware
    public function report_approval()
    {
        return view('ticketings.report.report_approval');
    }
    public function report_ticket_hardware()
    {
        return view('ticketings.report.report_ticket_hardware');
    }
    public function data_report_ticket_hardware(Request $request)
    {
        // Validasi input
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
            'week'  => 'required|integer',
        ]);

        $year  = $request->year;
        $month = $request->month;
        $week  = $request->week;

        // range minggu
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, 31);

        $data = DB::table('tbl_tickets as a')
            ->selectRaw("
                c.id_departemen,
                c.nama_departemen,
                SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END) AS manpower,
                SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END) AS hardware,
                SUM(CASE WHEN a.jenis_problem = 'network' THEN 1 ELSE 0 END) AS network,
                SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END) AS software,
                SUM(CASE WHEN a.status_problem = 'closed' THEN 1 ELSE 0 END) AS solved,
                SUM(CASE WHEN a.status_problem <> 'closed' THEN 1 ELSE 0 END) AS unsolved,
                COUNT(*) AS total
            ")
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->where('a.jenis_ticket', 'hardware')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_departemen', 'c.nama_departemen')
            ->orderBy('c.id_departemen')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan untuk periode yang dipilih.',
                'data' => []
            ]);
        }

        $sumTotals = [
            'sum_manpower' => 0,
            'sum_hardware' => 0,
            'sum_network'  => 0,
            'sum_software' => 0,
        ];

        foreach ($data as $row) {
            $sumTotals['sum_manpower'] += (int) $row->manpower;
            $sumTotals['sum_hardware'] += (int) $row->hardware;
            $sumTotals['sum_network']  += (int) $row->network;
            $sumTotals['sum_software'] += (int) $row->software;
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data'    => $data,
            'sum_totals'  => $sumTotals
        ]);
    }
    public function data_chart_ticket_hardware(Request $request)
    {
        // Validasi input
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
            'week'  => 'required|integer',
        ]);

        $year  = $request->year;
        $month = $request->month;
        $week  = $request->week;

        // ===============================
        // Range minggu
        // ===============================
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, 31);

        // ===============================
        // BAR CHART (Solved vs Unsolved)
        // ===============================
        $barChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->selectRaw("
                SUM(CASE WHEN a.status_problem = 'closed' THEN 1 ELSE 0 END) AS solved,
                SUM(CASE WHEN a.status_problem <> 'closed' THEN 1 ELSE 0 END) AS unsolved
            ")
            ->where('a.jenis_ticket', 'hardware')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->first();

        // ===============================
        // PIE (Jenis Departemen)
        // ===============================
        $pieChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->select(
                'c.nama_departemen',
                DB::raw('COUNT(*) AS total')
            )
            ->where('a.jenis_ticket', 'hardware')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_departemen')
            ->orderBy('c.id_departemen')
            ->get();

        // ===============================
        // DOUGHNUT (Jenis Problem)
        // ===============================
        $doughnutChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->selectRaw("
                SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END) AS sum_manpower,
                SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END) AS sum_hardware,
                SUM(CASE WHEN a.jenis_problem = 'network'  THEN 1 ELSE 0 END) AS sum_network,
                SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END) AS sum_software
            ")
            ->where('a.jenis_ticket', 'hardware')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->first();

        // ===============================
        // LINE CHART (Plant)
        // ===============================
        $lineChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('plants as c', 'b.plant_id', '=', 'c.id_plant')
            ->select(
                'c.nama_plant',
                DB::raw('COUNT(*) AS total')
            )
            ->where('a.jenis_ticket', 'hardware')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_plant')
            ->orderBy('c.id_plant')
            ->get();

        // ===============================
        // RESPONSE JSON
        // ===============================
        return response()->json([
            'success' => true,
            'filter'  => [
                'year'  => $year,
                'month' => $month,
                'week'  => $week,
                'start_day' => $startDay,
                'end_day'   => $endDay
            ],
            'bar'    => $barChart,
            'pie'    => $pieChart,
            'donut'  => $doughnutChart,
            'line'   => $lineChart
        ]);
    }




    public function report_ticket_software()
    {
        return view('ticketings.report.report_ticket_software');
    }
    public function data_report_ticket_software(Request $request)
    {
        // Validasi input
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
            'week'  => 'required|integer',
        ]);

        $year  = $request->year;
        $month = $request->month;
        $week  = $request->week;

        // range minggu
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, cal_days_in_month(CAL_GREGORIAN, $month, $year));


        $data = DB::table('tbl_tickets as a')
            ->selectRaw("
                c.id_departemen,
                c.nama_departemen,
                SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END) AS manpower,
                SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END) AS hardware,
                SUM(CASE WHEN a.jenis_problem = 'network' THEN 1 ELSE 0 END) AS network,
                SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END) AS software,
                SUM(CASE WHEN a.status_problem = 'closed' THEN 1 ELSE 0 END) AS solved,
                SUM(CASE WHEN a.status_problem <> 'closed' THEN 1 ELSE 0 END) AS unsolved,
                COUNT(*) AS total
            ")
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->where('a.jenis_ticket', 'software')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_departemen', 'c.nama_departemen')
            ->orderBy('c.id_departemen')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan untuk periode yang dipilih.',
                'data' => []
            ]);
        }

        $sumTotals = [
            'sum_manpower' => 0,
            'sum_hardware' => 0,
            'sum_network'  => 0,
            'sum_software' => 0,
        ];
        foreach ($data as $row) {
            $sumTotals['sum_manpower'] += (int) $row->manpower;
            $sumTotals['sum_hardware'] += (int) $row->hardware;
            $sumTotals['sum_network']  += (int) $row->network;
            $sumTotals['sum_software'] += (int) $row->software;
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data'    => $data,
            'sum_totals'  => $sumTotals
        ]);
    }
    public function data_chart_ticket_software(Request $request)
    {
        // Validasi input
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
            'week'  => 'required|integer',
        ]);

        $year  = $request->year;
        $month = $request->month;
        $week  = $request->week;

        // ===============================
        // Range minggu
        // ===============================
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, cal_days_in_month(CAL_GREGORIAN, $month, $year));


        // ===============================
        // BAR CHART (Solved vs Unsolved)
        // ===============================
        $barChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->selectRaw("
                SUM(CASE WHEN a.status_problem = 'closed' THEN 1 ELSE 0 END) AS solved,
                SUM(CASE WHEN a.status_problem <> 'closed' THEN 1 ELSE 0 END) AS unsolved
            ")
            ->where('a.jenis_ticket', 'software')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->first();

        // ===============================
        // PIE (Jenis Departemen)
        // ===============================
        $pieChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->select(
                'c.nama_departemen',
                DB::raw('COUNT(*) AS total')
            )
            ->where('a.jenis_ticket', 'software')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_departemen')
            ->orderBy('c.id_departemen')
            ->get();

        // ===============================
        // DOUGHNUT (Jenis Problem)
        // ===============================
        $doughnutChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->selectRaw("
                SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END) AS sum_manpower,
                SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END) AS sum_hardware,
                SUM(CASE WHEN a.jenis_problem = 'network'  THEN 1 ELSE 0 END) AS sum_network,
                SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END) AS sum_software
            ")
            ->where('a.jenis_ticket', 'software')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->first();

        // ===============================
        // LINE CHART (Plant)
        // ===============================
        $lineChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('plants as c', 'b.plant_id', '=', 'c.id_plant')
            ->select(
                'c.nama_plant',
                DB::raw('COUNT(*) AS total')
            )
            ->where('a.jenis_ticket', 'software')
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_plant')
            ->orderBy('c.id_plant')
            ->get();

        // ===============================
        // RESPONSE JSON
        // ===============================
        return response()->json([
            'success' => true,
            'filter'  => [
                'year'  => $year,
                'month' => $month,
                'week'  => $week,
                'start_day' => $startDay,
                'end_day'   => $endDay
            ],
            'bar'    => $barChart,
            'pie'    => $pieChart,
            'donut'  => $doughnutChart,
            'line'   => $lineChart
        ]);
    }


    public function create_report_ticket(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'week' => 'required|integer',
            'jenis_ticket' => 'required|string',
        ]);
        $username       = Auth::user()->username;
        $plantId        = Auth::user()->plant_id;
        $departemenId   = Auth::user()->departemen_id;
        $year           = $request->year;
        $month          = $request->month;
        $week           = $request->week;
        $startDay       = ($week - 1) * 7 + 1;
        $endDay         = min($startDay + 6, cal_days_in_month(CAL_GREGORIAN, $month, $year));
        $jenis_ticket   = $request->jenis_ticket;

        //cek tbl_tickets
        $tickets = DB::table('tbl_tickets as a')
            ->where('a.jenis_ticket', $jenis_ticket)
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->get();
        if ($tickets->isEmpty())
            return response()->json(['success' => false, 'message' => 'Tidak ada data ticket untuk periode yang dipilih']);
        if (ReportTicket::where(compact('year', 'month', 'week', 'jenis_ticket'))->exists())
            return response()->json(['success' => false, 'message' => 'Data reporting periode ini sudah pernah dibuat']);

        //cek userhirarki
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
        $approvers = [
            'approver_level2'   => null,
            'approver_level3'   => null,
            'status_level2'     => null,
            'status_level3'     => null,
            'date_level2'       => null,
            'date_level3'       => null,
        ];
        $user_level = null;
        $level1_users = json_decode($hirarki->level1, true);

        // Tentukan level user login
        if (in_array($username, $level1_users)) {
            $user_level    = 1;
        } elseif ($username == $hirarki->level2) {
            $user_level   = 2;
        } elseif ($username == $hirarki->level3) {
            $user_level   = 3;
        }

        // Tentukan approver berdasarkan level user login
        switch ($user_level) {
            case 1:
                $approvers['approver_level2'] = $hirarki->level2;
                $approvers['approver_level3'] = $hirarki->level3;
                break;
            case 2:
                $approvers['approver_level3'] = $hirarki->level3;
                break;
            case 3:
                $approvers['approver_level3'] = $username;
                $approvers['status_level3'] = true;
                $approvers['date_level3'] = now();
                break;
            default:
                dd($username, $hirarki->level1, $hirarki->level2, $hirarki->level3, $hirarki->level4);
                return response()->json(['message' => 'Username tidak ada di hirarki'], 400);
        }

        $approval_flow = [
            2 => $approvers['approver_level2'],
            3 => $approvers['approver_level3'],
        ];

        $next_approver = null;
        foreach ($approval_flow as $level => $approver) {
            if ($level > $user_level && !empty($approver) && $approver !== '-') {
                $next_approver = $approver;
                break;
            }
        }
        DB::beginTransaction();
        try {

            ReportTicket::create([
                'year' => $year,
                'month' => $month,
                'week' => $week,
                'jenis_ticket' => $jenis_ticket,
                'user_create' => $username,
                'approver_level2' => $approvers['approver_level2'],
                'approver_level3' => $approvers['approver_level3'],
                'status_ticket' => 'waiting',
            ]);

            $ticket_no = 'RPT/' . $week . '/' . $month . '/' . $year;
            $message = "Report ticket {$jenis_ticket} ({$ticket_no}) menunggu approval.";
            ActivityLogger::log('create', 'Ticket', 'Primary: ' . $ticket_no);
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
                    "Report ticket ($ticket_no) disetujui karena berada di level tertinggi approval."
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reporting ticket berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function data_report_approval(Request $request)
    {
        
        $user = Auth::user();
        $username = $user->username;
        $isIT  = ($user->departemen_id == 3);

        $isLeaderImp        = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1006]));
        $isLeaderTs         = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1007]));
        $isAsmenIt          = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1001]));

        if (!($isAsmenIt || $isLeaderImp ||  $isLeaderTs )) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat report approval',
                'data'    => []
            ]);
        }
        $query = ReportTicket::query();

        if ($request->status_ticket) $query->where('status_ticket', $request->status_ticket);
        if ($isAsmenIt) {
    // Asmen IT bisa lihat semua ticket: software + hardware, jadi tidak perlu filter
        } elseif ($isLeaderImp) {
            $query->where('jenis_ticket', 'software');
        } elseif ($isLeaderTs) {
            $query->where('jenis_ticket', 'hardware');
        }
        $data = $query->orderByDesc('created_at')->get();
        if ($data->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data report approval tidak ditemukan', 'data' => []]);
        }

        foreach ($data as $ticket) {
            $ticket->need_approve = false;

            $approvalFlow = [];
            if (!empty($ticket->approver_level2)) $approvalFlow[2] = ['username' => $ticket->approver_level2, 'status' => $ticket->status_level2];
            if (!empty($ticket->approver_level3)) $approvalFlow[3] = ['username' => $ticket->approver_level3, 'status' => $ticket->status_level3];

            if (isset($approvalFlow[2]) && is_null($approvalFlow[2]['status']) && $username === $approvalFlow[2]['username']) {
                $ticket->need_approve = true;
            } elseif (isset($approvalFlow[3]) && is_null($approvalFlow[3]['status']) && $username === $approvalFlow[3]['username']) {
                $level2_ok = !isset($approvalFlow[2]) || $approvalFlow[2]['status'] === true;
                if ($level2_ok) $ticket->need_approve = true;
            }
        }

        return response()->json(['success' => true, 'message' => 'Data report approval berhasil diambil', 'data' => $data]);
    }



    public function proses_approval_report_ticket(Request $request)
    {
        // Validasi data dari AJAX
        $request->validate([
            'status'        => 'required|in:approved,rejected',
            'year'          => 'required|integer',
            'month'         => 'required|integer',
            'week'          => 'required|integer',
            'jenis_ticket'  => 'required|string',
        ]);
        $pengguna_login = Auth::user()->username;
        $plant_id_login = Auth::user()->plant_id;
        $status       = $request->status;
        $year         = $request->year;
        $month        = $request->month;
        $week         = $request->week;
        $jenis_ticket = $request->jenis_ticket;
        $ticket_no = 'RPT/' . $week . '/' . $month . '/' . $year;

        $report_ticket = ReportTicket::where('year', $year)
            ->where('month', $month)
            ->where('week', $week)
            ->where('jenis_ticket', $jenis_ticket)
            ->first();

        if (!$report_ticket) {
            return response()->json(['success' => false, 'message' => 'Report Tiket tidak ditemukan']);
        }
        // Ambil user pembuat tiket
        $user_pembuat = User::where('username', $report_ticket->user_create)->first();
        if (!$user_pembuat) {
            return response()->json(['success' => false, 'message' => 'User pembuat tidak ditemukan']);
        }

        $waktu_sekarang = now();
        $update_data = [];
        DB::beginTransaction();

        try {
            if (!empty($report_ticket->approver_level2) && $pengguna_login == $report_ticket->approver_level2) {
                if (is_null($report_ticket->status_level2) || $report_ticket->status_level2 === '') {
                    $update_data['status_level2'] = $request->status == 'approved' ? true : ($request->status == 'rejected' ? false : null);
                    $update_data['date_level2'] = $waktu_sekarang;
                    $update_data['status_ticket'] = $request->status == 'approved' ? 'waiting' : 'rejected';

                    $penerima_selanjutnya = $report_ticket->approver_level3 ?? $report_ticket->user_create;
                    if ($request->status == 'approved') {
                        $pesan = "Report Tiket $ticket_no membutuhkan approval berikutnya.";
                        NotificationHelper::send($ticket_no, $penerima_selanjutnya, $plant_id_login, $pesan);

                        $pesan_user = "Report Tiket $ticket_no telah disetujui pada tahap 1.";
                        NotificationHelper::send($ticket_no, $report_ticket->user_create, $plant_id_login, $pesan_user);
                    } else {
                        $pesan = "Report Tiket $ticket_no anda telah ditolak.";
                        NotificationHelper::send($ticket_no, $report_ticket->user_create, $plant_id_login, $pesan);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'Level 2 sudah diapprove'], 403);
                }
            }
            if (!empty($report_ticket->approver_level3) && $pengguna_login == $report_ticket->approver_level3) {
                // Cek level sebelumnya jika ada
                if (!empty($report_ticket->approver_level2) && $report_ticket->status_level2 !== TRUE) {
                    return response()->json(['success' => false, 'message' => 'Level sebelumnya belum approve'], 403);
                }

                if (is_null($report_ticket->status_level3) || $report_ticket->status_level3 === '') {
                    $update_data['status_level3'] = $request->status == 'approved' ? true : ($request->status == 'rejected' ? false : null);
                    $update_data['date_level3'] = $waktu_sekarang;
                    $update_data['status_ticket'] = $request->status == 'approved' ? 'approved' : 'rejected';

                    $pesan = $request->status == 'approved'
                        ? "Report Tiket anda($ticket_no) telah FULL APPROVED."
                        : "Report Tiket anda ($ticket_no) DITOLAK oleh $pengguna_login pada Level 3.";
                    NotificationHelper::send($ticket_no, $report_ticket->user_create, $plant_id_login, $pesan);
                } else {
                    return response()->json(['success' => false, 'message' => 'Level 3 sudah diapprove'], 403);
                }
            }
            if (empty($update_data)) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak approval'], 403);
            }

            DB::table('report_tickets')
                ->where('year', $year)
                ->where('month', $month)
                ->where('week', $week)
                ->where('jenis_ticket', $jenis_ticket)
                ->update($update_data);

            ActivityLogger::log($request->status === 'approved' ? 'approve' : 'reject', 'Report Tiket', 'Primary: ' . $ticket_no);


            DB::commit();

            return response()->json(['success' => true, 'message' => 'Approval berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses tiket: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function export_excel(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'week' => 'required|integer',
            'jenis_ticket' => 'required|string',
        ]);

        $year = $request->year;
        $month = $request->month;
        $week = $request->week;
        $jenis_ticket = $request->jenis_ticket;

        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $data = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->selectRaw("
            c.nama_departemen,
            COALESCE(SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END), 0) AS manpower,
            COALESCE(SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END), 0) AS hardware,
            COALESCE(SUM(CASE WHEN a.jenis_problem = 'network'  THEN 1 ELSE 0 END), 0) AS network,
            COALESCE(SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END), 0) AS software,
            COALESCE(SUM(CASE WHEN a.status_problem = 'closed' THEN 1 ELSE 0 END), 0) AS solved,
            COALESCE(SUM(CASE WHEN a.status_problem <> 'closed' THEN 1 ELSE 0 END), 0) AS unsolved,
            COUNT(*) AS total
        ")
            ->where('a.jenis_ticket', $jenis_ticket)   // 🔥 DINAMIS
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.nama_departemen')
            ->orderBy('c.nama_departemen')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tidak ditemukan untuk periode yang dipilih.',
                'data' => []
            ]);
        }

        $reportTicket = ReportTicket::query()
            ->from('report_tickets as a')
            ->leftJoin('users as b', 'b.username', '=', 'a.user_create') // USER CREATE
            ->leftJoin('plants as e', 'e.id_plant', '=', 'b.plant_id')
            ->leftJoin('departemens as f', 'f.id_departemen', '=', 'b.departemen_id')
            ->leftJoin('positions as g', 'g.id_position', '=', 'b.position_id')
            ->leftJoin('users as c', 'c.username', '=', 'a.approver_level2') // APPROVER LEVEL 2
            ->leftJoin('plants as h', 'h.id_plant', '=', 'c.plant_id')
            ->leftJoin('departemens as i', 'i.id_departemen', '=', 'c.departemen_id')
            ->leftJoin('positions as j', 'j.id_position', '=', 'c.position_id')
            ->leftJoin('users as d', 'd.username', '=', 'a.approver_level3') // APPROVER LEVEL 3
            ->leftJoin('plants as k', 'k.id_plant', '=', 'd.plant_id')
            ->leftJoin('departemens as l', 'l.id_departemen', '=', 'd.departemen_id')
            ->leftJoin('positions as m', 'm.id_position', '=', 'd.position_id')
            ->where('a.jenis_ticket', $jenis_ticket)
            ->where('a.year', $year)
            ->where('a.month', $month)
            ->where('a.week', $week)
            ->select(
                'a.*',
                'b.nama_lengkap as user_create_name',
                'e.nama_plant as user_create_plant',
                'f.nama_departemen as user_create_departemen',
                'g.nama_position as user_create_position',
                'c.nama_lengkap as approver_level2_name',
                'h.nama_plant as approver_level2_plant',
                'i.nama_departemen as approver_level2_departemen',
                'j.nama_position as approver_level2_position',
                'd.nama_lengkap as approver_level3_name',
                'k.nama_plant as approver_level3_plant',
                'l.nama_departemen as approver_level3_departemen',
                'm.nama_position as approver_level3_position'
            )
            ->first();
        // Folder QR
        $qrDir = storage_path('app/temp_qr');
        if (!file_exists($qrDir)) {
            mkdir($qrDir, 0777, true);
        }
        $uniq = uniqid();

        $qrUserCreatePath = $qrDir . "/qr_user_create_$uniq.png";
        $qrApproverL2Path = $qrDir . "/qr_approver_l2_$uniq.png";
        $qrApproverL3Path = $qrDir . "/qr_approver_l3_$uniq.png";

        // Data QR
        $qrUserCreateText     = 'Nama: ' . $reportTicket->user_create_name . "\nPosisi: " . $reportTicket->user_create_position . "\nDept: " . $reportTicket->user_create_departemen . "\nPlant: " . $reportTicket->user_create_plant;
        $qrApproverLevel2Text = 'Nama: ' . $reportTicket->approver_level2_name . "\nPosisi: " . $reportTicket->approver_level2_position . "\nDept: " . $reportTicket->approver_level2_departemen . "\nPlant: " . $reportTicket->approver_level2_plant;
        $qrApproverLevel3Text = 'Nama: ' . $reportTicket->approver_level3_name . "\nPosisi: " . $reportTicket->approver_level3_position . "\nDept: " . $reportTicket->approver_level3_departemen . "\nPlant: " . $reportTicket->approver_level3_plant;

        // Generate QR PNG
        Builder::create()->writer(new PngWriter())->data($qrUserCreateText)->size(150)->build()->saveToFile($qrUserCreatePath);
        Builder::create()->writer(new PngWriter())->data($qrApproverLevel2Text)->size(150)->build()->saveToFile($qrApproverL2Path);
        Builder::create()->writer(new PngWriter())->data($qrApproverLevel3Text)->size(150)->build()->saveToFile($qrApproverL3Path);

        // Optional: hapus QR setelah generate
        register_shutdown_function(function () use ($qrUserCreatePath, $qrApproverL2Path, $qrApproverL3Path) {
            @unlink($qrUserCreatePath);
            @unlink($qrApproverL2Path);
            @unlink($qrApproverL3Path);
        });
        // ===============================
        // PIE (Jenis Departemen)
        // ===============================
        $pieChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->leftJoin('departemens as c', 'b.departemen_id', '=', 'c.id_departemen')
            ->select(
                'c.nama_departemen',
                DB::raw('COUNT(*) AS total')
            )
            ->where('a.jenis_ticket', $jenis_ticket)
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->groupBy('c.id_departemen')
            ->orderBy('c.id_departemen')
            ->get();

        // ===============================
        // DOUGHNUT (Jenis Problem)
        // ===============================
        $doughnutChart = DB::table('tbl_tickets as a')
            ->join('users as b', 'b.username', '=', 'a.user_create')
            ->selectRaw("
                SUM(CASE WHEN a.jenis_problem = 'manpower' THEN 1 ELSE 0 END) AS chart_sum_manpower,
                SUM(CASE WHEN a.jenis_problem = 'hardware' THEN 1 ELSE 0 END) AS chart_sum_hardware,
                SUM(CASE WHEN a.jenis_problem = 'network'  THEN 1 ELSE 0 END) AS chart_sum_network,
                SUM(CASE WHEN a.jenis_problem = 'software' THEN 1 ELSE 0 END) AS chart_sum_software
            ")
            ->where('a.jenis_ticket', $jenis_ticket)
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->first();
        // dd($data, $pieChart, $doughnutChart);
        return Excel::download(
            new TicketReportExport(
                $data,
                $reportTicket,
                $qrUserCreatePath,
                $qrApproverL2Path,
                $qrApproverL3Path,
                $pieChart,
                $doughnutChart,
                $week,
                $month,
                $year
            ),
            "ticket_{$jenis_ticket}_{$year}_{$month}_week{$week}.xlsx"
        );
    }
}
