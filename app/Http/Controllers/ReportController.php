<?php

namespace App\Http\Controllers;
use App\Models\ReportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  

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
                'success' => true,
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
                'success' => true,
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

        $year = $request->year;
        $month = $request->month;
        $week = $request->week;
        $jenis_ticket = $request->jenis_ticket;

        $startDay = ($week - 1) * 7 + 1;
        $endDay   = min($startDay + 6, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $tickets = DB::table('tbl_tickets as a')
            ->where('a.jenis_ticket', $jenis_ticket)
            ->whereYear('a.tgl_permintaan', $year)
            ->whereMonth('a.tgl_permintaan', $month)
            ->whereRaw('EXTRACT(DAY FROM a.tgl_permintaan) BETWEEN ? AND ?', [$startDay, $endDay])
            ->get();

        if ($tickets->isEmpty())
            return response()->json(['success' => false, 'message' => 'Tidak ada data ticket untuk periode yang dipilih']);

        if (ReportTicket::where(compact('year','month','week','jenis_ticket'))->exists())
            return response()->json(['success' => false, 'message' => 'Data reporting periode ini sudah pernah dibuat']);

        $approver = DB::table('users as a')
            ->join('departemens as b','a.departemen_id','=','b.id_departemen')
            ->join('positions as c','a.position_id','=','c.id_position')
            ->whereIn(DB::raw('LOWER(c.nama_position)'), ['ass. manager','asst. manager','ass.man'])
            ->whereRaw('LOWER(b.nama_departemen)=?', ['it'])
            ->value('a.username');

        DB::beginTransaction();
        try {
            ReportTicket::create([
                'year' => $year,
                'month' => $month,
                'week' => $week,
                'jenis_ticket' => $jenis_ticket,
                'approver' => $approver,
                'status_approval' => null,
                'date_approval' => null,
                'status_ticket' => 'waiting',
            ]);

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
        $query = ReportTicket::select(
                'id','year','month','week','jenis_ticket',
                'approver','status_ticket','status_approval',
                'date_approval','created_at'
            );

        if ($request->status_ticket) {
            $query->where('status_ticket', $request->status_ticket);
        }

        $data = $query->orderByDesc('created_at')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data report approval tidak ditemukan',
                'data'    => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data report approval berhasil diambil',
            'data'    => $data
        ]);
    }


    public function proses_approval_report_ticket(Request $request)
    {
    }

}
