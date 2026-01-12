<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #000;
            font-size: 8pt;
            vertical-align: top;
            word-wrap: break-word;
        }
    </style>
</head>

<body>

    <!-- ================= HEADER ================= -->
    <table style="width:100%; table-layout:fixed;">
        <tr>
            <!-- KIRI -->
            <td style="width:35%; border:none; text-align:left;">
                <img src="file://{{ public_path('assets/img/logo/1.png') }}" style="height:20px;"><br>
                <span style="font-size:7pt; font-weight:bold;">
                    PT. Banshu Electric Indonesia
                </span>
            </td>

            <!-- TENGAH -->
            <td style="width:30%; text-align:center; vertical-align:middle; font-size:11pt; font-weight:bold;">
                Form Claim Software
            </td>

            <!-- KANAN -->
            <td style="width:35%; border:none;">&nbsp;</td>
        </tr>
    </table>


    <!-- ================= INFO ================= -->
    <table style="margin-top:2mm;">
        <tr>
            <td width="33%">
                <table>
                    <tr>
                        <td style="border:none;">Kepada</td>
                        <td style="border:none;">: IT Departemen</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Dari</td>
                        <td style="border:none;">: {{ $data->nama_pemohon }}</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Tanggal</td>
                        <td style="border:none;">
                            : {{ \Carbon\Carbon::parse($data->tgl_permintaan)->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none;">Nama Software</td>
                        <td style="border:none;">: {{ $data->nama_software }}</td>
                    </tr>
                </table>
            </td>
            <td width="33%">
                <table>
                    <tr>
                        <td style="border:none;">Plant :</td>
                        <td style="border:none;"> {!! $data->label === 'BEI' ? '☑' : '☐' !!} BEI </td>
                    </tr>
                    <tr>
                        <td style="border:none;"></td>
                        <td style="border:none;">{!! $data->label === 'BRI' ? '☑' : '☐' !!} BRI </td>
                    </tr>
                    <tr>
                        <td style="border:none;"></td>
                        <td style="border:none;">{!! $data->label === 'KI' ? '☑' : '☐' !!} KI </td>
                    </tr>
                    <tr>
                        <td style="border:none;"></td>
                        <td style="border:none;">{!! $data->label === 'PTI' ? '☑' : '☐' !!} PTI </td>
                    </tr>
                </table>
            </td>
            <td width="33%">
                <table>
                    <tr>
                        <td style="border:none;">Kategori :</td>
                        <td style="border:none;">
                            {!! $data->kategori_klaim === 'ui' ? '☑' : '☐' !!} UI
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none;"></td>
                        <td style="border:none;">
                            {!! $data->kategori_klaim === 'function' ? '☑' : '☐' !!} Function
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none;"></td>
                        <td style="border:none;">
                            {!! $data->kategori_klaim === 'output' ? '☑' : '☐' !!} Output
                        </td>
                    </tr>
                </table>
            </td>

        </tr>
    </table>

    <!-- ================= TABEL KLAIM ================= -->
    <table style="margin-top:2mm; table-layout:fixed; width:100%;">
        <tr>
            <th rowspan="2" style="width:4%;">No</th>
            <th colspan="3" style="width:50%;">Diisi Oleh Pemohon</th>
            <th colspan="2" style="width:20%;">Diisi Oleh IT</th>
            <th rowspan="2" style="width:8%;">Status</th>
            <th rowspan="2" style="width:18%;">Keterangan</th>
        </tr>
        <tr>
            <th style="width:18%;">Deskripsi Klaim</th>
            <th style="width:22%;">Lampiran</th>
            <th style="width:10%;">PIC</th>
            <th style="width:10%;">PIC</th>
            <th style="width:10%;">Target</th>
        </tr>

        {{-- DATA UTAMA --}}
        <tr>
            <td rowspan="3" style="text-align:center;">1</td>
            <td rowspan="3">{{ $data->deskripsi }}</td>
            <td style="text-align:center;">
                @if (!empty($data->file1))
                    @php
                        $path = public_path($data->file1); // Path fisik untuk mPDF (image)
                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); // Extension file
                    @endphp
                    @if (file_exists($path) && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) 
                        <a href="{{ asset($data->file1) }}"> <img src="file://{{ $path }}" style="height:12mm;"></a>
                    @elseif (file_exists($path)) {{-- DOCUMENT (LINK) --}}
                        <a href="{{ asset($data->file1) }}" style="display:inline-block; padding:2mm 3mm; border:0.2mm solid #000; font-size:9px; font-weight:bold;text-decoration:none; color:#000;">
                            {{ strtoupper($ext) }}
                        </a>
                    @else
                        <span style="font-size:9px; color:red;">FILE NOT FOUND</span>
                    @endif
                @endif
            </td>
            <td rowspan="3" style="text-align:center;">
                {{ $finalPemohon['diperiksa']->nama_lengkap ?? ($finalPemohon['diketahui']->nama_lengkap ?? '-') }}</td>
            <td rowspan="3" style="text-align:center;">{{ $data->nama_it }}</td>
            <td rowspan="3" style="text-align:center;">
                {{ $data->time_finish ? \Carbon\Carbon::parse($data->time_finish)->format('d-m-Y') : '-' }}</td>
            <td rowspan="3" style="text-align:center;">{{ $data->status_problem }}</td>
            <td rowspan="3">{{ $data->remarks }}</td>
        </tr>
        {{-- LAMPIRAN 2 --}}
        <tr>
            <td style="text-align:center;">
                @if (!empty($data->file2))
                    @php
                        $path = public_path($data->file2); // Path fisik untuk mPDF (image)
                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); // Extension file
                    @endphp
                    @if (file_exists($path) && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) 
                        <a href="{{ asset($data->file2) }}"> <img src="file://{{ $path }}" style="height:12mm;"></a>
                    @elseif (file_exists($path)) {{-- DOCUMENT (LINK) --}}
                        <a href="{{ asset($data->file2) }}" style="display:inline-block; padding:2mm 3mm; border:0.2mm solid #000; font-size:9px; font-weight:bold;text-decoration:none; color:#000;">
                            {{ strtoupper($ext) }}
                        </a>
                    @else
                        <span style="font-size:9px; color:red;">FILE NOT FOUND</span>
                    @endif
                @endif
            </td>
        </tr>
        {{-- LAMPIRAN 3 --}}
        <tr>
            <td style="text-align:center;">
                @if (!empty($data->file3))
                    @php
                        $path = public_path($data->file3); // Path fisik untuk mPDF (image)
                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); // Extension file
                    @endphp
                    @if (file_exists($path) && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) 
                        <a href="{{ asset($data->file3) }}"> <img src="file://{{ $path }}" style="height:12mm;"></a>
                    @elseif (file_exists($path)) {{-- DOCUMENT (LINK) --}}
                        <a href="{{ asset($data->file3) }}" style="display:inline-block; padding:2mm 3mm; border:0.2mm solid #000; font-size:9px; font-weight:bold;text-decoration:none; color:#000;">
                            {{ strtoupper($ext) }}
                        </a>
                    @else
                        <span style="font-size:9px; color:red;">FILE NOT FOUND</span>
                    @endif
                @endif
            </td>
        </tr>

        {{-- BARIS KOSONG (FIXED TINGGI) --}}
        @for ($i = 0; $i < 13; $i++)
            <tr>
                <td rowspan="3">&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
                <td>&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
                <td rowspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        @endfor
    </table>

    <!-- ================= FOOTER (TTD) ================= -->
    <htmlpagefooter name="footerTTD">
        <div style="width:110mm;margin-left:auto;">
            <table style="width:100%;border-collapse:collapse;table-layout:fixed;font-size:6pt;text-align:center;">

                <!-- HEADER -->
                <tr>
                    <th colspan="3" style="border:1px solid #000;width:48%;vertical-align:middle;">IT</th>
                    <th style="border:none;width:4%;"></th>
                    <th colspan="3" style="border:1px solid #000;width:48%;vertical-align:middle;">Pemohon</th>
                </tr>

                <!-- SUB HEADER -->
                <tr>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Diketahui</th>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Diperiksa</th>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Diterima</th>
                    <th style="border:none;width:4%;"></th>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Diketahui</th>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Diperiksa</th>
                    <th style="border:1px solid #000;width:16%;vertical-align:middle;">Dibuat</th>
                </tr>

                <!-- QR -->
                <tr>
                    {{-- IT --}}
                    <td style="border:1px solid #000;width:16%;padding:2mm;text-align:center;">
                        @if ($finalIt['diketahui_qr'])
                            <img src="{{ $finalIt['diketahui_qr'] }}" style="height:12mm;">
                        @endif
                    </td>
                    <td style="border:1px solid #000;width:16%;padding:2mm;text-align:center;">
                        @if ($finalIt['diperiksa_qr'])
                            <img src="{{ $finalIt['diperiksa_qr'] }}" style="height:12mm;">
                        @endif
                    </td>
                    <td style="border:1px solid #000;width:16%;padding:2mm;text-align:center;">
                        <img src="{{ $qrItFinish }}" style="height:12mm;">
                    </td>
                    <td style="border:none;"></td>
                    {{-- PEMOHON --}}
                    <td
                        style="border:1px solid #000;width:16%;padding:2mm;text-align:center;vertical-align:middle;height:12mm;">
                        @if ($finalPemohon['diketahui_qr'])
                            <img src="{{ $finalPemohon['diketahui_qr'] }}" style="height:12mm;">
                        @else
                            -
                        @endif
                    </td>
                    <td
                        style="border:1px solid #000;width:16%;padding:2mm;text-align:center;vertical-align:middle;height:12mm;">
                        @if ($finalPemohon['diperiksa_qr'])
                            <img src="{{ $finalPemohon['diperiksa_qr'] }}" style="height:12mm;">
                        @else
                            -
                        @endif
                    </td>
                    <td style="border:1px solid #000;width:16%;padding:2mm;text-align:center;">
                        <img src="{{ $qrPemohon }}" style="height:12mm;">
                    </td>
                </tr>

                <!-- NAMA -->
                <tr>
                    {{-- IT --}}
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $finalIt['diketahui']->nama_lengkap ?? '-' }}</td>
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $finalIt['diperiksa']->nama_lengkap ?? '-' }}</td>
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $data->nama_it }}</td>
                    <td style="border:none;width:4%;"></td>
                    {{-- PEMOHON --}}
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $finalPemohon['diketahui']->nama_lengkap ?? '-' }}</td>
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $finalPemohon['diperiksa']->nama_lengkap ?? '-' }}</td>
                    <td style="border:1px solid #000;text-align:center;vertical-align:middle;">{{ $data->nama_pemohon }}</td>
                </tr>

            </table>
        </div>
    </htmlpagefooter>

    <sethtmlpagefooter name="footerTTD" value="on" />
</body>

</html>
