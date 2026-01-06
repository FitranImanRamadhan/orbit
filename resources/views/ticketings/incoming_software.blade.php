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
            <div class="col-md-2" id="div-klaim">
                <label for="kategori_klaim" class="form-label fw-semibold">Kategori Klaim</label>
                <select id="kategori_klaim" class="form-select">
                    <option value="" selected disabled>pilih disini</option>
                    <option value="ui">UI</option>
                    <option value="function">Function</option>
                    <option value="output">Output</option>
                </select>
            </div>
            <div class="col-md-2" id="div-problem">
                <label for="filter_status_problem" class="form-label fw-semibold">Status</label>
                <select id="filter_status_problem" class="form-select">
                    <option value="" selected disabled>pilih disini</option>
                    <option value="open">Open</option>
                    <option value="on_progress">On Progress</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end mt-2">
                <button class="btn btn-secondary me-2" onclick="resetFilter()"><i class="bi bi-arrow-clockwise me-1"></i>
                    Reset Form </button>
                <button class="btn btn-primary" onclick="applyFilter()"><i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </div>

        <div>
            <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left">
                <thead class="table-dark">
                    <tr>
                        <th>No. Ticket</th>
                        <th>Item</th>
                        <th>Dept</th>
                        <th>Plant</th>
                        <th>Nama</th>
                        <th>Kategori Klaim</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        {{-- <th>Form Klaim</th> --}}
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
                    <h5 class="modal-title fw-semibold" id="label_ticketno">Detail Ticket</h5>
                    <input type="text" id="ticketno" hidden>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="d-flex justify-content-start align-items-center mb-3">
                        <a id="btnStart" href="javascript:void(0)" class="btn btn-primary btn-sm me-2"
                            onclick="btnStart()"> START </a>
                        <span id="start_time" class="fw-semibold text-secondary"></span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label id="label_deskripsi" class="form-label fw-semibold"></label>
                            <textarea class="form-control" rows="12" disabled id="deskripsi" style="resize:none;"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6"><label class="form-label fw-semibold">Nama Software</label><input
                                        type="text" class="form-control" id="software_name" disabled></div>
                                <div class="col-md-6" id="div-klaim">
                                    <label for="jenis_problem" class="form-label fw-semibold">Jenis Problem</label>
                                    <select id="jenis_problem" class="form-select">
                                        <option value="" disabled>-- Jenis Problem --</option>
                                        <option value="hardware">Hardware</option>
                                        <option value="manpower">Manpower</option>
                                        <option value="network">Network</option>
                                        <option value="software">Software</option>
                                    </select>
                                </div>
                            </div>
                            <div id="div_software" class="row g-3 mb-3">
                                <div class="col-md-6"><label class="form-label fw-semibold">Kategori Klaim</label>
                                  <input type="text" class="form-control" id="fkategori_klaim" disabled>
                                </div>
                            </div>
                            <div id="lampiran_container" class="mb-3">
                                <label class="form-label fw-semibold">Lampiran</label>
                                <div class="d-flex gap-2 flex-wrap overflow-auto" style="max-height:120px;"
                                    id="lampiran_files">
                                    <!-- File akan di-inject via JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start align-items-center mt-3">
                        <a id="btnFinish" href="javascript:void(0)" class="btn btn-warning btn-sm me-2 disabled"
                            onclick="btnFinish()"> FINISH </a>
                        <span id="finish_time" class="fw-semibold text-secondary"></span>
                    </div>
                    <hr>
                    <div class="mt-2"><label class="form-label fw-semibold">Add Comment Below</label>
                        <div class="border rounded-3 p-2 bg-light" id="chat_container"
                            style="height:200px; overflow-y:auto;"></div>
                        <div class="input-group mt-2">
                            <input type="text" class="form-control" id="chat_input" placeholder="Tulis pesan...">
                            <input type="file" class="form-control" id="chat_file" style="max-width:120px;">
                            <button class="btn btn-primary" id="btnSendChat"><i class="fa fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewPdfModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalTitle">Preview Form Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <iframe
                    id="iframePreviewPdf"
                    src=""
                    width="100%"
                    height="600"
                    style="border:none;">
                </iframe>
            </div>

            <!-- INI YANG TADI BELUM ADA -->
            <div class="modal-footer">
                <a href="#" id="btnDownloadPdf" class="btn btn-success">
                    <i class="bi bi-download"></i> Download PDF
                </a>
            </div>

        </div>
    </div>
</div>


@endsection

@section('scripts')
    <script>
        let jenis_ticket = 'software';
        let table;
        $(document).ready(function() {
            jenis_ticket = 'software';
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
                columns: [
                    { data: 'ticket_no', width: "120px"},
                    { data: 'nama_software', width: "150px" },
                    { data: 'nama_departemen', width: "80px" },
                    { data: 'nama_plant', width: "150px"},
                    { data: 'nama_lengkap', width: "150px" },
                    { 
                      data: 'kategori_klaim', 
                      className: 'text-start', 
                      width: "100px",
                      render: function(data, type, row) {
                          switch(data) {
                              case 'ui': return 'UI';
                              case 'function': return 'Function';
                              case 'output': return 'Output';
                              default: return data; // fallback
                          }
                      }
                    },
                    { data: 'tgl_permintaan', width: "120px"},
                    { data: 'status_problem',
                        render: function(data) {
                            let badge = '';
                            switch (data) {
                                case 'open': badge = '<span class="badge bg-danger text-dark">Open</span>'; break;
                                case 'on_progress': badge = '<span class="badge bg-warning">On_progress</span>'; break;
                                case 'closed': badge = '<span class="badge bg-success">Closed</span>'; break;
                                case 'canceled': badge = '<span class="badge bg-secondary">Canceled</span>'; break;
                                default: badge = '<span class="badge bg-secondary">'+ '-' +'</span>'; break;
                            }
                            return badge;
                        },
                        className: 'text-center'
                    }
                    ,
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
                                <button 
                                    class="btn btn-sm btn-warning me-1 btn-preview rounded"
                                    data-id="${row.id}">
                                    Preview <i class="bi bi-search ms-1"></i>
                                </button>
                            `;
                        },
                        orderable: false,
                        searchable: false,
                        className: "text-left",
                        width: "100px"
                    }

                ]
            });
            $('#tabel tbody').on('dblclick', 'tr', function() {
                let rowData = table.row($(this).closest('tr')).data();
                if (!rowData) return;

                $('#label_ticketno').text('Detail Ticket: ' + rowData.ticket_no);
                $('#ticketno').val(rowData.ticket_no ?? '');
                $('#deskripsi').val(rowData.deskripsi ?? '');
                $('#nama').val(rowData.nama_lengkap ?? '');
                $('#departemen').val(rowData.nama_departemen ?? '');
                $('#software_name').val(rowData.nama_software ?? '');
                $('#jenis_problem').val(rowData.jenis_problem ?? '').trigger('change');

                const kategoriMap = {
                    'ui': 'UI',
                    'function': 'Function',
                    'output': 'Output'
                };
                $('#fkategori_klaim').val(kategoriMap[rowData.kategori_klaim] ?? rowData.kategori_klaim);
                const lampiranContainer = $('#lampiran_files');
                lampiranContainer.empty(); // kosongkan container sebelum menambahkan file baru

                ['file1', 'file2', 'file3'].forEach(fileField => {
                    if (!rowData[fileField]) return;
                    const fileUrl = '/' + rowData[fileField];
                    if (!fileUrl) return;

                    const ext = fileUrl.split('.').pop().toLowerCase();
                    const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    const docTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

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
                    if (rowData.time_finish) $('#finish_time').text(rowData.time_finish);
                    if (rowData.time_start) $('#start_time').text(rowData.time_start);
                } else { // OPEN
                    $('#btnStart').removeClass('disabled');
                    $('#btnFinish').addClass('disabled');
                    setFormDisabled(true);
                }
            });

            $('#detailTicketModal').on('hidden.bs.modal', function () {
                $('#jenis_problem').val('');
                $('#fkategori_klaim').val('');
                $('#start_time').text('');
                $('#finish_time').text('');
                $('#btnStart').removeClass('disabled');
                $('#btnFinish').addClass('disabled');
                setFormDisabled(true);

                startTime = null;
                finishTime = null;
            });

            let currentPdfId = null;

            $('#tabel').on('click', '.btn-preview', function () {
                currentPdfId = $(this).data('id');
                let rowData = table.row($(this).closest('tr')).data();
                if (!currentPdfId) return  Swal.fire('Info', 'ID Tidak Ditemukan', 'info');
                if (rowData.status_problem !== 'closed') return  Swal.fire('Info', 'Ticket Belum selesai dikerjakan', 'info');

                $('#iframePreviewPdf').attr(
                    'src',
                    `/ticketings/incoming-software/pdf/preview?id=${currentPdfId}&mode=preview`
                );

                new bootstrap.Modal('#previewPdfModal').show();
            });

            $('#btnDownloadPdf').on('click', function () {
                if (!currentPdfId) return;

                window.open(
                    `/ticketings/incoming-software/pdf/preview?id=${currentPdfId}&mode=download`,
                    '_blank'
                );
            });

            $('#previewPdfModal').on('hidden.bs.modal', function () {
                $('#iframePreviewPdf').attr('src', '');
                currentPdfId = null;
            });
        });

        function applyFilter() {
            let start_date = $('#filter_start').val();
            let end_date = $('#filter_end').val();
            let status_problem = $('#filter_status_problem').val();
            let katergori_klaim = $('#kategori_klaim').val();
            $.ajax({
                url: '/ticketings/data_incoming_software',
                type: 'POST',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    jenis_ticket: jenis_ticket,
                    status_problem: status_problem,
                    kategori_klaim: katergori_klaim
                },
                success: function(res) {
                    if (res.success) {
                        table.clear().rows.add(res.data).draw();
                    } else {
                        Swal.fire('Info', res.message, 'info');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal mengambil data', 'error');
                }
            });
        }

        function resetFilter() {
            $('#filter_start').val('');
            $('#filter_end').val('');
            $('#kategori_klaim').val('');
            $('#filter_status_problem').val('');
            $('#departemen_id').val('');
            if (table) {
                table.clear().draw();
            }
        }

        function btnStart() {
          setFormDisabled(false);
          $('#btnStart').addClass('disabled');
          $('#btnFinish').removeClass('disabled');

          startTime = new Date();
          const dbTimestamp = formatDateToDB(startTime);
          $('#start_time').text(dbTimestamp);

          $.ajax({
              url: '/ticketings/sw_start_proses',
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
            };

            $.ajax({
                url: '/ticketings/sw_finish_proses',
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

        function loadDept() {
            $.ajax({
                url: '/ticketings/getDept',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    let dropdown = $('#departemen_id');
                    dropdown.empty();
                    dropdown.append('<option value="">-- Pilih Departemen --</option>');

                    $.each(res.data, function(i, dept) {
                        dropdown.append(
                            `<option value="${dept.id_departemen}">${dept.nama_departemen}</option>`
                            );
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
            $('#jenis_problem').prop('disabled', isDisabled);
            $('#chat_input').prop('disabled', isDisabled);
            $('#chat_file').prop('disabled', isDisabled);
            $('#btnSendChat').prop('disabled', isDisabled);
        }
    </script>
@endsection
