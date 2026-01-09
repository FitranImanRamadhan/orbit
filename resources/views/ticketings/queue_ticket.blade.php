@extends('layout')

@section('content')
    <div class="row g-3 justify-content-start mb-3">
        <div class="col-md-2">
            <label for="filter_start" class="form-label fw-semibold">Tanggal Mulai</label>
            <input type="date" id="filter_start" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="filter_end" class="form-label fw-semibold">Tanggal Selesai</label>
            <input type="date" id="filter_end" class="form-control">
        </div>
    </div>
    <div class="row g-3 justify-content-start mb-3 align-items-end">
    <div class="col-md-2">
    <label for="departemen_id" class="form-label fw-semibold">Departemen</label>
        <select class="form-select shadow-sm" id="departemen_id" name="departemen_id" required>
        </select>
    </div>
    <div class="col-md-2">
      <label for="filter_jenis" class="form-label fw-semibold">Jenis Ticket</label>
      <select id="filter_jenis" class="form-select">
        <option value="" selected disabled>pilih disini</option>
        <option value="software">Software</option>
        <option value="hardware">Hardware</option>
      </select>
    </div>
    <div class="col-md-2">
      <label for="filter_status_approval" class="form-label fw-semibold">Status Approval</label>
      <select id="filter_status_approval" class="form-select">
        <option value="" selected disabled>pilih disini</option>
        <option value="waiting">Waiting</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
    <div class="col-md-auto d-flex align-items-end gap-2">
        <button class="btn btn-secondary" onclick="resetFilter()"><i class="bi bi-arrow-clockwise me-1"></i>Reset Form</button>
        <button class="btn btn-primary" onclick="applyFilter()"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
    </div>
</div>
<div class="table-responsive">
    <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" style="width:100%">
        <thead class="table-dark">
           <tr>
                <th style="text-align:left;">No. Ticket</th>
                <th style="text-align:left;">Nama</th>
                <th style="text-align:left;">Department</th>
                <th style="text-align:left;">Tanggal</th>
                <th style="text-align:left;">Jenis Ticket</th>
                <th style="text-align:left;">Status Approval</th>
                <th style="text-align:left;">Status Problem</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
let table;
$(document).ready(function() {
    loadDept();
    $('#filter_start').val('');
    $('#filter_end').val('');
    $('#filter_jenis').val('');
    $('#filter_status_approval').val('');
    $('#filter_status_problem').val('');
    // Inisialisasi DataTable kosong
    table = $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        columns: [
            { data: 'ticket_no', width: "185px"}, 
            { data: 'nama_lengkap', width: "170px" },
            { data: 'nama_departemen', className: 'text-start'},
            { data: 'tgl_permintaan', className: 'text-start', width: "150px"}, 
            { data: 'jenis_ticket',
                render: function(data) {
                    switch (data) {
                        case 'software': return 'Software';
                        case 'hardware': return 'Hardware';
                        default: return data;
                    }
                }
            },
            { data: 'status_approval',
                render: function(data) {
                    let badge = '';
                    switch (data) {
                        case 'waiting': badge = '<span class="badge bg-warning text-dark">Waiting</span>'; break;
                        case 'approved': badge = '<span class="badge bg-success">Approved</span>'; break;
                        case 'rejected': badge = '<span class="badge bg-danger">Rejected</span>'; break;
                        default: badge = '<span class="badge bg-secondary">-</span>'; break;
                    }
                    return badge;
                },
                className: 'text-center'
            },
            { data: 'status_problem',
                render: function(data) {
                    let badge = '';
                    switch (data) {
                        case 'open': badge = '<span class="badge bg-danger text-dark">Open</span>'; break;
                        case 'on_progress': badge = '<span class="badge bg-warning">On Progres</span>'; break;
                        case 'closed': badge = '<span class="badge bg-success">Closed</span>'; break;
                        default: badge = '<span class="badge bg-secondary">-</span>'; break;
                    }
                    return badge;
                },
                className: 'text-center'
            }
        ]
    });
});

function setErrorFocus(selector) {
    $(selector)
        .css('border', '1px solid red')
        .focus()
        .one('input change keyup click', function () {
            $(this).css('border', '');
        });
}

function applyFilter() {
  let filter_jenis = $('#filter_jenis').val();
  if (filter_jenis === '' || filter_jenis === null) { setErrorFocus('#filter_jenis'); Swal.fire('Perhatian', 'Silakan pilih jenis ticket terlebih dahulu.', 'warning'); return;}

  let start_date = $('#filter_start').val();
  let end_date = $('#filter_end').val();
  let jenis_ticket = $('#filter_jenis').val();
  let status_approval = $('#filter_status_approval').val();
  let departemen = $('#departemen_id').val();
  console.log(jenis_ticket, status_approval, departemen);
  $.ajax({
      url: '/ticketings/data_queue',
      type: 'POST',
      data: { start_date: start_date, end_date: end_date, jenis_ticket: jenis_ticket, 
              status_approval: status_approval, departemen: departemen },
      success: function(res) {
          if(res.success){table.clear().rows.add(res.data).draw();} 
          else {Swal.fire('Error', res.message, 'error');}
      },
      error: function() {Swal.fire('Error', 'Gagal mengambil data', 'error');}
  });
}


function resetFilter() {
    $('#filter_start').val('');
    $('#filter_end').val('');
    $('#filter_jenis').val('');
    $('#filter_status_approval').val('');
    $('#filter_status_problem').val('');
    if (table) {table.clear().draw();}
}

function loadDept() {
  $.ajax({
      url: '/ticketings/getDept',
      type: 'POST',
      dataType: 'json',
      success: function(res) {
          let dropdown = $('#departemen_id');
          dropdown.empty();
          dropdown.append('<option value="" selected disabled>departemen</option>');
          $.each(res.data, function(i, dept) {
              dropdown.append(`<option value="${dept.id_departemen}">${dept.nama_departemen}</option>`);
          });
      },
      error: function(xhr) {
          console.error(xhr.responseText);
          Swal.fire('Error', 'Gagal memuat data departemen', 'error');
      }
  });
}
</script>
@endsection
