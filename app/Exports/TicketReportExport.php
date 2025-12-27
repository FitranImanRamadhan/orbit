<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;

use PhpOffice\PhpSpreadsheet\Chart\Title;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use Maatwebsite\Excel\Concerns\WithCharts;



class TicketReportExport implements FromCollection, WithEvents, WithTitle
{
    protected $week;
    protected $month;
    protected $year;
    protected $data;
    protected $pieChart;
    protected $doughnutChart;

    public function __construct($data, $pieChart, $doughnutChart, $week, $month, $year)
    {
        $this->data          = $data;
        $this->pieChart      = $pieChart;
        $this->doughnutChart = $doughnutChart;
        $this->week          = $week;
        $this->month         = $month;
        $this->year          = $year;
    }

    private function formatPeriode()
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April',   5 => 'Mei',      6 => 'Juni',
        7 => 'Juli',    8 => 'Agustus',  9 => 'September',
        10 => 'Oktober',11 => 'November',12 => 'Desember'
    ];

    return 'Minggu ke-' . $this->week . ' ' .
           $bulan[$this->month] . ' ' .
           $this->year;
}

    /* =========================
       ISI EXCEL
    ========================= */
    public function collection()
    {
        $rows = collect();
        // Baris kosong (1–9) — MANUAL
        $rows->push(['', 'REPORT DATA PERMASALAN', '', '', '', '', '', '', '', '', '', 'Doc No   :']);
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', 'Rev          : -']);
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', 'Eff Date :']);
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', 'Page        : 1 of 2']);
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', '']); // 5
        $rows->push(['Plant       : Purwakarta', '', '', '', '', '', '', '', '', '', '', '']); // 6
        $rows->push(['Periode :', '', '', '', '', '', '', '', '', '', '', '']); // 7
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', '']); // 8
        $rows->push(['', '', '', '', '', '', '', '', '', '', '', '']); // 9
        $rows->push(['Departemen', 'Total Problem', '', '', '', 'Total', '', '']); //10
        $rows->push(['', 'Manpower', 'Hardware', 'Network', 'Software', 'Solved', 'Un-Solved', 'Keseluruhan']); //11

        // Data mulai baris 12
        foreach ($this->data as $d) {
            $rows->push([
                $d->nama_departemen ?? '',
                (int) ($d->manpower ?? 0),
                (int) ($d->hardware ?? 0),
                (int) ($d->network ?? 0),
                (int) ($d->software ?? 0),
                (int) ($d->solved ?? 0),
                (int) ($d->unsolved ?? 0),
                (int) ($d->total ?? 0),
            ]);
        }


        return $rows;
    }

    /* =========================
       STYLE & FOOTER
    ========================= */
   public function registerEvents(): array
        {
            return [
                AfterSheet::class => function (AfterSheet $event) {

                    $sheet = $event->sheet->getDelegate();

                    /* ===== BARIS ===== */
                    $headerRow1 = 10;
                    $headerRow2 = 11;
                    $dataStart  = 12;
                    $lastRow    = $sheet->getHighestRow();
                    $footerRow  = $lastRow + 1;
                    $signRow    = $footerRow + 3;

                    /* ===== BORDER LUAR ===== */
                    $sheet->getStyle('A1:M' . ($signRow + 7))->applyFromArray([
                        'borders' => [
                            'outline' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    $sheet->mergeCells('L1:M1');
                    $sheet->mergeCells('L2:M2');
                    $sheet->mergeCells('L3:M3');
                    $sheet->mergeCells('L4:M4');
                    $event->sheet->getStyle('A1:M4')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                    /* ===== HEADER ATAS ===== */
                    $sheet->mergeCells('A1:A4');
                    $sheet->mergeCells('B1:K4');
                    $sheet->getStyle('A1:K4')->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'font' => ['bold' => true],
                    ]);
                    $sheet->getStyle('B1')->getFont()->setSize(16);

                    /* ===== LOGO ===== */
                    $sheet->mergeCells('A1:A3');

                    $logo = new Drawing();
                    $logo->setName('Logo Banshu');
                    $logo->setPath(public_path('assets/img/logo/1.png'));
                    $logo->setHeight(45);              // ⬅️ PENTING: kecilkan
                    $logo->setCoordinates('A1');
                    $logo->setOffsetX(120);             // ⬅️ geser ke tengah
                    $logo->setOffsetY(5);
                    $logo->setWorksheet($sheet);


                   $sheet->setCellValue('A4', 'PT BANSHU ELECTRIC INDONESIA');
                    $sheet->getStyle('A4')->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'font' => [
                            'bold' => true,
                            'size' => 9,
                        ],
                    ]);

                    $sheet->setCellValue(
                        'A7',
                        'Periode : ' . $this->formatPeriode()
                    );


                    /* ===== HEADER TABEL ===== */
                    $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                    $sheet->mergeCells("B{$headerRow1}:E{$headerRow1}");
                    $sheet->mergeCells("F{$headerRow1}:H{$headerRow1}");
                    $sheet->getStyle("A{$headerRow1}:H{$headerRow2}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '212529'],
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    /* ===== FOOTER TOTAL ===== */
                    $sheet->setCellValue("A{$footerRow}", 'Total Per Problem');
                    foreach (['B','C','D','E'] as $col) {
                        $sheet->setCellValue(
                            "{$col}{$footerRow}",
                            "=SUM({$col}{$dataStart}:{$col}{$lastRow})"
                        );
                    }
                    $sheet->getStyle("A{$footerRow}:H{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E9ECEF'],
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    

                    /* ===== TANDA TANGAN ===== */
                    $labels = ['G'=>'Approved','I'=>'Checked','K'=>'Prepared'];
                    $names  = ['G'=>'Agus Hidayat','I'=>'Ahmad Yani','K'=>'Kirin J'];
                    $titles = ['G'=>'IT Manager','I'=>'Leader','K'=>'Adm IT'];

                    foreach ($labels as $col => $text) {
                        $next = chr(ord($col) + 1);

                        $sheet->mergeCells("{$col}{$signRow}:{$next}{$signRow}");
                        $sheet->setCellValue("{$col}{$signRow}", $text);

                        $sheet->mergeCells("{$col}".($signRow+1).":{$next}".($signRow+4));
                        $sheet->mergeCells("{$col}".($signRow+5).":{$next}".($signRow+5));
                        $sheet->mergeCells("{$col}".($signRow+6).":{$next}".($signRow+6));

                        $sheet->setCellValue("{$col}".($signRow+5), $names[$col]);
                        $sheet->setCellValue("{$col}".($signRow+6), $titles[$col]);
                    }

                    $sheet->getStyle("G{$signRow}:L".($signRow+6))->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'font' => ['bold' => true, 'size' => 9],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    /* ===== BORDER DATA & WIDTH ===== */
                    $sheet->getStyle("A{$headerRow1}:H{$footerRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    foreach (range('A','H') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }
            ];
        }




    /* =========================
       NAMA SHEET
    ========================= */
    public function title(): string
    {
        return 'Ticket Report';
    }
}
