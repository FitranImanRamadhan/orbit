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
    <div class="col-md-2">
      <label for="filter_status_problem" class="form-label fw-semibold">Status Problem</label>
      <select id="filter_status_problem" class="form-select">
        <option value="" selected disabled>pilih disini</option>
        <option value="open">Open</option>
        <option value="on_progress">On Progress</option>
        <option value="closed">Closed</option>
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

<div class="modal fade" id="detailTicketModal" tabindex="-1" aria-labelledby="detailTicketLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-semibold" id="label_ticketno">Detail Ticket</h5>
        <input type="text" id="ticketno" hidden>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
        <div class="row g-4">
          <div class="col-md-6">
            <label id="label_deskripsi" class="form-label fw-semibold"></label>
            <textarea class="form-control bg-light" rows="12" readonly id="deskripsi" style="resize:none;"></textarea>
          </div>
          <div class="col-md-6">
            <div id="div_software" class="row g-3 mb-3">
              <div class="col-md-6"><label class="form-label fw-semibold">Kategori Klaim</label><input type="text" class="form-control bg-light" id="kategori_klaim" readonly></div>
              <div class="col-md-6"><label class="form-label fw-semibold">Nama Software</label><input type="text" class="form-control bg-light" id="software_name" readonly></div>
            </div>
            <div id="div_hardware" class="row g-3 mb-3">
              <div class="col-md-6"><label class="form-label fw-semibold">Nama Hardware</label><input type="text" class="form-control bg-light" id="hardware_name" readonly></div>
            </div>
            <div id="lampiran_container" class="mb-3">
              <label class="form-label fw-semibold">Lampiran</label>
              <div class="d-flex gap-2 flex-wrap overflow-auto" style="max-height:120px;" id="lampiran_files">
                <!-- File akan di-inject via JS -->
              </div>
            </div>
          </div>
          <div class="mt-2" id="containerRemarks">
                  <h6>Remarks</h6>
                <div class="d-flex text-muted mb-1" style="font-size:12px;" id="rowRemark2">
                    <strong id="nama_remark2" class="me-1"></strong>
                    <span id="remark2"></span>
                </div>

                <div class="d-flex text-muted mb-1" style="font-size:12px;" id="rowRemark3">
                    <strong id="nama_remark3" class="me-1"></strong>
                    <span id="remark3"></span>
                </div>

                <div class="d-flex text-muted mb-1" style="font-size:12px;" id="rowRemark4">
                    <strong id="nama_remark4" class="me-1"></strong>
                    <span id="remark4"></span>
                </div>
            </div>
        </div>
        <hr>
        @include('components.chat')
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
let table;
$(document).ready(function() {
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
            { data: 'nama_departemen'},
            { data: 'tgl_permintaan', width: "150px"}, 
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
                        case 'on_progress': badge = '<span class="badge bg-warning">On Progress</span>'; break;
                        case 'closed': badge = '<span class="badge bg-success">Closed</span>'; break;
                        case 'canceled': badge = '<span class="badge bg-secondary">Closed</span>'; break;
                        default: badge = '<span class="badge bg-secondary">-</span>'; break;
                    }
                    return badge;
                },
                className: 'text-center'
            }
        ]
    });
    $('#tabel tbody').on('dblclick', 'tr', function() {
        let rowData = table.row(this).data(); 
        if (!rowData) return;
      
        $('#label_ticketno').text('Detail Ticket: ' + rowData.ticket_no);
        $('#ticketno').val(rowData.ticket_no ?? '');
        $('#deskripsi').val(rowData.deskripsi ?? '');
        $('#nama').val(rowData.nama_lengkap ?? '');
        $('#departemen').val(rowData.nama_departemen ?? '');

        $('#containerRemarks').hide();
        $('#rowRemark2, #rowRemark3, #rowRemark4').hide();
        $('#remark2, #remark3, #remark4').text('');
        $('#nama_remark2, #nama_remark3, #nama_remark4').text('');
        
        if (rowData.remarks2) {
            $('#nama_remark2').text(rowData.nama_lengkap2);
            $('#remark2').text(' : ' +rowData.remarks2);
            $('#rowRemark2').show();
            $('#containerRemarks').show();
        }
        if (rowData.remarks3) {
            $('#nama_remark3').text(rowData.nama_lengkap3);
            $('#remark3').text(' : ' +rowData.remarks3);
            $('#rowRemark3').show();
            $('#containerRemarks').show();
        }
        if (rowData.remarks4) {
            $('#nama_remark4').text(rowData.nama_lengkap4);
            $('#remark4').text(' : ' +rowData.remarks4);
            $('#rowRemark4').show();
            $('#containerRemarks').show();
        }

        let jenis_ticket = rowData.jenis_ticket;
        if (jenis_ticket == 'software') {
          $('#div_software').show();
          $('#label_deskripsi').text('Deskripsi Klaim');
          $('#div_hardware').hide();
          $('#software_name').val(rowData.nama_software ?? '');
          $('#kategori_klaim').val(rowData.kategori_klaim ?? '');
        }else{
          $('#div_hardware').show();
          $('#label_deskripsi').text('Deskripsi Keluhan');
          $('#div_software').hide();
          $('#hardware_name').val(rowData.nama_hardware ?? ''); 
        }
       const lampiranContainer = $('#lampiran_files');
          lampiranContainer.empty(); // kosongkan container sebelum menambahkan file baru

          ['file1', 'file2', 'file3'].forEach(fileField => {
              if (!rowData[fileField]) return;
              const fileUrl = '/' + rowData[fileField];
              if (!fileUrl) return;

              const ext = fileUrl.split('.').pop().toLowerCase();
              const imageTypes = ['jpg','jpeg','png','gif','webp'];
              const docTypes = ['pdf','doc','docx','xls','xlsx'];

              let icon = 'fa-file'; // default icon
              if (imageTypes.includes(ext)) icon = 'fa-image';
              else if (docTypes.includes(ext)) icon = 'fa-file-alt';

              lampiranContainer.append(`
                  <a href="${fileUrl}" target="_blank" class="btn btn-outline-secondary me-1 mb-1 d-flex flex-column align-items-center" style="width:100px; height:100px; justify-content:center; text-align:center;">
                      <i class="fa ${icon} fa-2x mb-1"></i>
                      <span style="font-size:12px; word-break:break-word;">${fileField.toUpperCase()}</span>
                  </a>
              `);
          });
        $('#detailTicketModal').modal('show');
        const ticketNo = $('#ticketno').val();
        // loadChat();
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
  let status_problem = $('#filter_status_problem').val();
  $.ajax({
      url: '/ticketings/data_track',
      type: 'POST',
      data: { start_date: start_date, end_date: end_date, jenis_ticket: jenis_ticket, 
              status_approval: status_approval, status_problem: status_problem },
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
</script>
@endsection
