@extends('layout')

@section('content')
<div id="card-data">
  <div class="row g-3 mb-3">
    <div class="col-md-2">
        <label for="filter_start" class="form-label fw-semibold">Tanggal Mulai</label>
        <input type="date" id="filter_start" class="form-control">
    </div>
    <div class="col-md-2">
        <label for="filter_end" class="form-label fw-semibold">Tanggal Selesai</label>
        <input type="date" id="filter_end" class="form-control">
    </div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-2" id="div-problem">
        <label for="ticket_No" class="form-label fw-semibold">No Ticket</label>
        <input type="text" id="ticket_no" class="form-control">
    </div>
    <div class="col-md-2" id="div-klaim">
        <label for="departemen_id" class="form-label fw-semibold">Departemen</label>
          <select class="form-select shadow-sm" id="departemen_id" name="departemen_id" required>
          </select>
    </div>
    <div class="col-12 d-flex justify-content-end mt-2">
        <button class="btn btn-secondary me-2" onclick="resetFilter()"><i class="bi bi-arrow-clockwise me-1"></i> Reset Form </button>
        <button class="btn btn-primary" onclick="applyFilter()"><i class="bi bi-funnel-fill me-1"></i> Filter </button>
    </div>
  </div>

  <div>
      <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" >
          <thead class="table-dark">
            <tr>
                <th>No. Ticket</th>
                <th>Nama Hardaware</th>
                <th>Dept</th>
                <th>Plant</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Status Perbaikan</th>
            </tr>
          </thead>
          <tbody></tbody>
      </table>
  </div>
</div>
<div class="modal fade" id="detailTicketModal" tabindex="-1" aria-labelledby="detailTicketLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-semibold" id="label_ticketno"></h5>
        <input type="text" id="ticketno" hidden>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
        <div class="d-flex justify-content-start align-items-center mb-3">
          <a id="btnStart" href="javascript:void(0)" class="btn btn-primary btn-sm me-2" onclick="btnStart()"> START </a>
          <span id="start_time" class="text-secondary"></span>
        </div>
        <div class="row g-3">
          <!-- Kolom 1 -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Nama Hardware</label>
            <input type="text" class="form-control" id="hardware_name" disabled>
            
            <label class="form-label fw-semibold mt-2">Jenis Keluhan</label>
            <textarea class="form-control" rows="5" id="deskripsi" disabled></textarea>
            
            <label class="form-label fw-semibold mt-2">Jenis Pengecekan</label>
            <textarea class="form-control" rows="5" id="jenis_pengecekan"></textarea>
          </div>

          <!-- Kolom 2 -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Jenis Problem</label>
            <select id="jenis_problem" class="form-select">
              <option value="" disabled selected>-- Jenis Problem --</option>
              <option value="hardware">Hardware</option>
              <option value="manpower">Manpower</option>
              <option value="network">Network</option>
              <option value="software">Software</option>
            </select>

            <label class="form-label fw-semibold mt-2">Counter Measure</label>
            <textarea class="form-control" rows="5" id="counter_measure"></textarea>
          </div>

          <!-- Kolom 3 -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Next Plan</label>
            <textarea class="form-control" rows="4" id="next_plan"></textarea>
            <div id="lampiran_container" class="mb-3">
              <label class="form-label fw-semibold">Lampiran</label>
              <div class="d-flex gap-2 flex-wrap overflow-auto" style="max-height:120px;" id="lampiran_files">
                <!-- File akan di-inject via JS -->
              </div>
            </div>
          </div>
        </div>

        <!-- Button Start / Finish -->
        <div class="d-flex justify-content-start align-items-center mt-3">
          <a id="btnFinish" href="javascript:void(0)" class="btn btn-warning btn-sm me-2 disabled" onclick="btnFinish()"> FINISH </a>
          <span id="finish_time" class="fw-semibold text-secondary"></span>
        </div>

        <hr>

        <!-- Chat Section -->
        @include('components.chat')

      </div>
    </div>
  </div>
</div>


@endsection

@section('scripts')
  <script>
    let jenis_ticket = 'hardware';
    let table;

    $(document).ready(function() {
      jenis_ticket = 'hardware';
      loadDept();
      $('#title1').text('Pilih Jenis Incoming Ticket');
      $("#reset").prop("disabled", true);
      $('#filter_start').val('');
      $('#filter_end').val('');
      $('#kategori_klaim').val('');
      $('#filter_status_problem').val('');
      $('#jenis_problem').val('');
      // Inisialisasi DataTable kosong
      table = $('#tabel').DataTable({
          processing: true,
          serverSide: false,
          scrollY: '400px',
          scrollX: true,
          scrollCollapse: true,
          ordering: false,
          columns: [
              { data: 'ticket_no', width: "180px"},
              { data: 'nama_hardware', width: "150px" },
              { data: 'nama_departemen', width: "80px" },
              { data: 'nama_plant', width: "150px"},
              { data: 'nama_lengkap', width: "150px" },
              { data: 'tgl_permintaan', width: "150px"},
              { data: 'status_problem',
                  render: function(data) {
                      let badge = '';
                      switch (data) {
                          case 'open': badge = '<span class="badge bg-danger text-dark">Open</span>'; break;
                          case 'on_progress': badge = '<span class="badge bg-warning">On Progress</span>'; break;
                          case 'closed': badge = '<span class="badge bg-success">Closed</span>'; break;
                          default: badge = '<span class="badge bg-secondary">'+ "-" +'</span>'; break;
                      }
                      return badge;
                  },
                  className: 'text-center'
              },
              { data: 'status_akhir_user',
                  render: function(data) {
                      let badge = '';
                      switch (data) {
                          case 'ng': badge = '<span class="badge bg-danger text-dark">NG</span>'; break;
                          case 'ok': badge = '<span class="badge bg-warning">OK</span>'; break;
                          default: badge = '<span class="badge bg-secondary">'+ "-" +'</span>'; break;
                      }
                      return badge;
                  },
                  className: 'text-center'
              },
          ]
      });

      $('#tabel tbody').on('dblclick', 'tr', function() {
          let rowData = table.row($(this).closest('tr')).data();
          if (!rowData) return;

          // Pasang data dasar
          $('#label_ticketno').text('Detail Ticket: ' + rowData.ticket_no);
          $('#ticketno').val(rowData.ticket_no || '');
          $('#deskripsi').val(rowData.deskripsi || '');
          $('#nama').val(rowData.nama_lengkap || '');
          $('#departemen').val(rowData.nama_departemen || '');
          $('#hardware_name').val(rowData.nama_hardware || '');
          $('#jenis_problem').val(rowData.jenis_problem || '').trigger('change');
          $('#counter_measure').val(rowData.counter_measure || '');
          $('#jenis_pengecekan').val(rowData.jenis_pengecekan || '');
          $('#next_plan').val(rowData.next_plan || '');
          $('#div_hardware').hide();

          // Kategori klaim mapping
          const kategoriMap = { ui: 'UI', function: 'Function', output: 'Output' };
          $('#fkategori_klaim').val(kategoriMap[rowData.kategori_klaim] || '');

          // Lampiran
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

          // if(rowData.status_approval !== 'approved'){
          //     Swal.fire('Warning','Ticket belum full approve, belum bisa dikerjakan!', 'warning');
          // } else {
              $('#detailTicketModal').modal('show'); // Bootstrap 4  
          // }

          // ====== STATUS HANDLING ======
          if (rowData.status_problem === 'on_progress') {
              $('#btnStart').addClass('disabled');
              $('#btnFinish').removeClass('disabled');
              setFormDisabled(false);
              if (rowData.time_start) {
                  $('#start_time').text(rowData.time_start);
                  startTime = new Date(rowData.time_start);
              }
          } else if (rowData.status_problem === 'closed') {
              $('#btnStart').addClass('disabled');
              $('#btnFinish').addClass('disabled');
              setFormDisabled(true);
              if (rowData.time_start) $('#start_time').text(rowData.time_start);
              if (rowData.time_finish) $('#finish_time').text(rowData.time_finish);
          } else { // OPEN
              $('#btnStart').removeClass('disabled');
              $('#btnFinish').addClass('disabled');
              setFormDisabled(true);
          }
      });


      $('#detailTicketModal').on('hidden.bs.modal', function () {
          $('#jenis_pengecekan').val('');
          $('#jenis_problem').val('');
          $('#counter_measure').val('');
          $('#next_plan').val('');
          $('#start_time').text('');
          $('#finish_time').text('');
          $('#btnStart').removeClass('disabled');
          $('#btnFinish').addClass('disabled');
          setFormDisabled(true);

          startTime = null;
          finishTime = null;
      });

    });

    function applyFilter() {
      let start_date = $('#filter_start').val();
      let end_date = $('#filter_end').val();
      let ticket_no = $('#ticket_no').val();
      let status_problem = $('#filter_status_problem').val();
      let departemen = $('#departemen_id').val();
      $.ajax({
          url: '/ticketings/data_incoming_hardware',
          type: 'POST',
          data: { start_date: start_date, end_date: end_date, ticket_no: ticket_no,
                  jenis_ticket: jenis_ticket, status_problem: status_problem, departemen: departemen },
          success: function(res) {
              if(res.success){table.clear().rows.add(res.data).draw();} 
              else {Swal.fire('Error', res.message, 'error');}
          },
          error: function() {Swal.fire('Error', 'Gagal mengambil data', 'error');}
      });
    }

    function btnStart() {
      setFormDisabled(false);
      $('#btnStart').addClass('disabled');
      $('#btnFinish').removeClass('disabled');

      startTime = new Date();
      const dbTimestamp = formatDateToDB(startTime);
      $('#start_time').text(dbTimestamp);

      $.ajax({
          url: '/ticketings/hw_start_proses',
          type: 'POST',
          data: {
              ticket_no: $('#ticketno').val(),
              start_time: dbTimestamp
          },
          success: function(res) {
              Swal.fire('Success', res.message || 'Proses dimulai', 'success');
              applyFilter();
          },
          error: function(xhr) {
              let message = xhr.responseJSON?.message || 'Terjadi kesalahan server';
              Swal.fire('Error', message, 'error');
          }
      });
    }


    function btnFinish() {
        finishTime = new Date();
        const dbTimestamp = formatDateToDB(finishTime);
        $('#finish_time').text(dbTimestamp);

        const finishData = {
            ticket_no: $('#ticketno').val(), 
            finish_time: dbTimestamp,
            jenis_problem: $('#jenis_problem').val(),
            counter_measure: $('#counter_measure').val(),
            next_plan: $('#next_plan').val(),
            jenis_pengecekan: $('#jenis_pengecekan').val()
        };

        $.ajax({
            url: '/ticketings/hw_finish_proses',
            type: 'POST',
            data: finishData,
            success: function(res) {
                Swal.fire('Success', res.message || 'Ticket selesai', 'success');
                $('#detailTicketModal').modal('hide');
                applyFilter();
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Terjadi kesalahan server';
                Swal.fire('Error', message, 'error');
            }
        });
    }


    function resetFilter() {
      $('#filter_start').val('');
      $('#filter_end').val('');
      $('#kategori_klaim').val('');
      $('#filter_status_problem').val('');
      $('#departemen_id').val('');
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


    let startTime = null;
    let finishTime = null;
    function formatDateToDB(date) {
        // Format: YYYY-MM-DD HH:MM:SS
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0'); // Month 0-11
        const dd = String(date.getDate()).padStart(2, '0');
        const hh = String(date.getHours()).padStart(2, '0');
        const mi = String(date.getMinutes()).padStart(2, '0');
        const ss = String(date.getSeconds()).padStart(2, '0');

        return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
    }

    function setFormDisabled(isDisabled) {
        $('#jenis_pengecekan').prop('disabled', isDisabled);
        $('#jenis_problem').prop('disabled', isDisabled);
        $('#counter_measure').prop('disabled', isDisabled);
        $('#next_plan').prop('disabled', isDisabled);
        $('#chat_input').prop('disabled', isDisabled);
        $('#chat_file').prop('disabled', isDisabled);
        $('#btnSendChat').prop('disabled', isDisabled);
    }

  </script>
@endsection
