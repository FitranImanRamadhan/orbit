<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; font-size: 11px; }
        table { width:100%; border-collapse: collapse; }
        td, th { border:1px solid #000; padding:4px; }
        .title { font-weight:bold; font-size:14px; text-align:center; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="4" class="title">FORM CLAIM SOFTWARE</td>
    </tr>
    <tr>
        <td>Ticket No</td>
        <td>{{ $data->ticket_no }}</td>
        <td>Tanggal</td>
        <td>{{ optional($data->tgl_permintaan)->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td>Software</td>
        <td>{{ $data->item_ticket }}</td>
        <td>Kategori</td>
        <td>{{ strtoupper($data->kategori_klaim) }}</td>
    </tr>
</table>

<br>

<table>
    <tr>
        <th width="5%">No</th>
        <th width="95%">Deskripsi</th>
    </tr>
    <tr>
        <td>1</td>
        <td>{{ $data->deskripsi }}</td>
    </tr>
</table>

</body>
</html>
