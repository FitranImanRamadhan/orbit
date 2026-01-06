@extends('layout')

@section('content')
<div class="row g-3 mb-3 align-items-end">
    <!-- Filters -->
    <div class="col-md-2">
        <label class="form-label fw-semibold">Year</label>
        <select id="filter_year" class="form-select">
            <option value="" disabled selected>Pilih Tahun</option>
            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--) : ?>
                <option value="<?= $y ?>"><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Month</label>
        <select id="filter_month" class="form-select">
            <option value="" disabled selected>Pilih Bulan</option>
            <?php for ($m = 1; $m <= 12; $m++) : ?>
                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Week</label>
        <select id="filter_week" class="form-select" disabled>
            <option value="" disabled selected>Pilih Minggu</option>
        </select>
    </div>

    <!-- Buttons -->
    <div class="col-md-auto d-flex align-items-end gap-2 ms-auto">
        <button class="btn btn-secondary" onclick="resetFilter()">
            <i class="bi bi-arrow-clockwise me-1"></i>Reset Form
        </button>
        <button class="btn btn-primary" onclick="applyFilter()">
            <i class="bi bi-funnel-fill me-1"></i>Filter
        </button>
        <a href="javascript:void(0)" 
        class="btn btn-success"
        id="btnReportingData"
        onclick="reportingData()">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Reporting Data
        </a>

        <a href="javascript:void(0)" class="btn btn-info" id="openChart" onclick="openChart()">
            <i class="bi bi-bar-chart-line me-1"></i>See Diagram
        </a>
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
    <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" style="width:100%">
        <thead class="table-dark">
            <tr>
                <th rowspan="2" class="align-middle">Departemen</th>
                <th colspan="4" class="text-center">Total Problem</th>
                <th colspan="3" class="text-center">Total</th>
            </tr>
            <tr>
                <th>Manpower</th>
                <th>Hardware</th>
                <th>Network</th>
                <th>Software</th>
                <th>Solved</th>
                <th>Un-Solved</th>
                <th>Keseluruhan</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot class="table-secondary">
            <tr>
                <th>Total Per Problem</th>
                <th id="sum_manpower"></th>
                <th id="sum_hardware"></th>
                <th id="sum_network"></th>
                <th id="sum_software"></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
@section('scripts')
    <script>
        let table;
        let problemChart = null;

    $(document).ready(function () {
        resetFilter();
        /* ===============================
        1. WEEK DROPDOWN (AUTO)
        =============================== */
        function updateWeek() {
            let year  = $('#filter_year').val();
            let month = $('#filter_month').val();
            let $week = $('#filter_week');

            $week.html('<option value="" disabled selected>Pilih Minggu</option>');

            if (!year || !month) {$week.prop('disabled', true); return;}

            let daysInMonth = new Date(year, month, 0).getDate();
            let totalWeek   = Math.ceil(daysInMonth / 7);

            for (let i = 1; i <= totalWeek; i++) {
                $week.append(`<option value="${i}">Week ${i}</option>`);
            }

            $week.prop('disabled', false);
        }

        $('#filter_year, #filter_month').on('change', updateWeek);


    /* ===============================
    2. DATATABLE INIT
    =============================== */
    table = $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        searching: false,
        ordering: true,
        columns: [
            { data: 'nama_departemen' },
            { data: 'manpower', className: 'text-center' },
            { data: 'hardware', className: 'text-center' },
            { data: 'network', className: 'text-center' },
            { data: 'software', className: 'text-center' },
            { data: 'solved', className: 'text-center' },
            { data: 'unsolved', className: 'text-center' },
            { data: 'total', className: 'text-center fw-semibold' },
        ]

    });
});



/* ===============================
   3. HELPER ERROR FOCUS
   =============================== */
function setErrorFocus(selector) {
$(selector)
    .css('border', '1px solid red')
    .focus()
    .one('input change keyup click', function () {
        $(this).css('border', '');
    });
}


/* ===============================
   4. APPLY FILTER
   =============================== */
function applyFilter() {

    let year  = $('#filter_year').val();
    let month = $('#filter_month').val();
    let week  = $('#filter_week').val();

    if (!year) {
        setErrorFocus('#filter_year');
        Swal.fire('Perhatian', 'Silakan pilih tahun', 'warning');
        return;
    }

    if (!month) {
        setErrorFocus('#filter_month');
        Swal.fire('Perhatian', 'Silakan pilih bulan', 'warning');
        return;
    }

    if (!week) {
        setErrorFocus('#filter_week');
        Swal.fire('Perhatian', 'Silakan pilih minggu', 'warning');
        return;
    }

    $.ajax({
        url: '/ticketings/report/data_report_ticket_hardware',
        type: 'POST',
        data: {
            year: year,
            month: month,
            week: week
        },
        success: function (res) {
            if (res.success) {
                 table.clear().rows.add(res.data).draw();

                // 👉 ISI FOOTER DARI BACKEND
                $('#sum_manpower').text(res.sum_totals.sum_manpower);
                $('#sum_hardware').text(res.sum_totals.sum_hardware);
                $('#sum_network').text(res.sum_totals.sum_network);
                $('#sum_software').text(res.sum_totals.sum_software);
            } else {
                 Swal.fire('Warning', res.message, 'warning');
            }
        },
        error: function (xhr) {
            let msg = xhr.responseJSON?.message || xhr.responseText || 'Terjadi Kesalahan Server';
            Swal.fire('Error', msg, 'error');
        }
    });
}

function reportingData() {
    let year  = $('#filter_year').val();
    let month = $('#filter_month').val();
    let week  = $('#filter_week').val();
    let jenis_ticket = 'hardware';

     if (!year) {
        setErrorFocus('#filter_year');
        Swal.fire('Perhatian', 'Silakan pilih tahun', 'warning');
        return;
    }

    if (!month) {
        setErrorFocus('#filter_month');
        Swal.fire('Perhatian', 'Silakan pilih bulan', 'warning');
        return;
    }

    if (!week) {
        setErrorFocus('#filter_week');
        Swal.fire('Perhatian', 'Silakan pilih minggu', 'warning');
        return;
    }

    Swal.fire({
        title: 'Simpan Reporting?',
        text: 'Data akan disimpan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ticketings/report/create_report_ticket',
                type: 'POST',
                dataType: 'json',
                data: {
                    year: year,
                    month: month,
                    week: week,
                    jenis_ticket: jenis_ticket,
                },
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success');
                    } else {
                        Swal.fire('Gagal', res.message, 'info');
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || xhr.responseText || 'Terjadi Kesalahan Server';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
}


function openChart() {
    let year  = $('#filter_year').val();
    let month = $('#filter_month').val();
    let week  = $('#filter_week').val();

     if (!year) {
        setErrorFocus('#filter_year');
        Swal.fire('Perhatian', 'Silakan pilih tahun', 'warning');
        return;
    }

    if (!month) {
        setErrorFocus('#filter_month');
        Swal.fire('Perhatian', 'Silakan pilih bulan', 'warning');
        return;
    }

    if (!week) {
        setErrorFocus('#filter_week');
        Swal.fire('Perhatian', 'Silakan pilih minggu', 'warning');
        return;
    }

    let url = `/ticketings/report/chart_ticket_hardware?year=${year}&month=${month}&week=${week}`;
    window.open(url, '_blank'); // buka tab baru
}


/* ===============================
   5. RESET FILTER
   =============================== */
function resetFilter() {
    $('#filter_year').val('');
    $('#filter_month').val('');
    $('#filter_week').html('<option value="" disabled selected>Pilih Minggu</option>').prop('disabled', true);
    if (table) {table.clear().draw();}
}
</script>
@endsection
