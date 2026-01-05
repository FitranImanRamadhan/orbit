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
        td, th {
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
            @if($data->file1)
                <img src="file://{{ public_path('storage/'.$data->file1) }}" style="height:12mm;">
            @endif
        </td>
        <td rowspan="3" style="text-align:center;">{{ $namaLevel4Pemohon }}</td>
        <td rowspan="3" style="text-align:center;">{{ $data->nama_it }}</td>
        <td rowspan="3" style="text-align:center;">{{ $data->time_finish ? \Carbon\Carbon::parse($data->time_finish)->format('d-m-Y'): '-' }}</td>
        <td rowspan="3" style="text-align:center;">{{ $data->status_problem }}</td>
        <td rowspan="3">{{ $data->remarks }}</td>
    </tr>
    {{-- LAMPIRAN 2 --}}
    <tr>
        <td style="text-align:center;">
            @if($data->file2)
                <img src="file://{{ public_path('storage/'.$data->file2) }}" style="height:12mm;">
            @endif
        </td>
    </tr>
    {{-- LAMPIRAN 3 --}}
    <tr>
        <td style="text-align:center;">
            @if($data->file3)
                <img src="file://{{ public_path('storage/'.$data->file3) }}" style="height:12mm;">
            @endif
        </td>
    </tr>

    {{-- BARIS KOSONG (FIXED TINGGI) --}}
    @for ($i = 0; $i < 15; $i++)
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
        <tr><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td></tr>
    @endfor
</table>




<!-- ================= FOOTER (TTD) ================= -->
<htmlpagefooter name="footerTTD">
    <table style="width:100%; table-layout:fixed; font-size:7pt;">
        <tr>
            <td style="width:38%; border:none;"></td>

            <!-- BLOK 3 TTD -->
            <td style="width:31%; border:none;">
                <table style="width:100%; table-layout:fixed; text-align:center; font-size:7pt;">
                    <tr><th colspan="3" style="font-size:7pt;">IT</th></tr>
                    <tr>
                        <td>Diketahui</td>
                        <td>Diperiksa</td>
                        <td>Diterima</td>
                    </tr>

                    <tr style="height:18mm;">
                        <td style="vertical-align:bottom;">
                            <img src="{{ $qrLevel3ItBase64 }}" style="height:12mm;">
                        </td>
                        <td style="vertical-align:bottom;">
                            <img src="{{ $qrLevel2ItBase64 }}" style="height:12mm;">
                        </td>
                        <td style="vertical-align:bottom;">
                           <img src="{{ $qrItFinishBase64 }}" style="height:12mm;">
                        </td>
                    </tr>

                    <tr>
                        <td style="white-space:nowrap;">{{ $namaLevel3It }}</td>
                        <td style="white-space:nowrap;">{{ $namaLevel2It }}</td>
                        <td style="white-space:nowrap;">{{ $data->nama_it }}</td>
                    </tr>
                </table>
            </td>

            <!-- BLOK 2 TTD -->
            <td style="width:31%; border:none;">
                <table style="width:100%; table-layout:fixed; text-align:center; font-size:7pt;">
                    <tr><th colspan="2" style="font-size:7pt;">Pemohon</th></tr>
                    <tr>
                        <td>Diketahui & Diperiksa</td>
                        <td>Dibuat</td>
                    </tr>
                    <tr style="height:18mm;">
                        <td style="vertical-align:bottom;">
                            <img src="{{ $qrLevel4PemohonBase64 }}" style="height:12mm;">
                        </td>
                        <td style="vertical-align:bottom;">
                           <img src="{{ $qrPemohonBase64 }}" style="height:12mm;">
                        </td>
                    </tr>

                    <tr>
                        <td style="white-space:nowrap;">{{ $namaLevel4Pemohon }}</td>
                        <td style="white-space:nowrap;">{{ $data->nama_pemohon }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</htmlpagefooter>
<sethtmlpagefooter name="footerTTD" value="on" />

</body>
</html>
