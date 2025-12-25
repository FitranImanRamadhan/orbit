@extends('layout')

@section('content')
    <div class="table-responsive">
        <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th style="text-align:left;">Tanggal</th>
                    <th style="text-align:left;">No. Ticket</th>
                    <th style="text-align:left;">Nama</th>
                    <th style="text-align:left;">Jenis Ticket</th>
                    <th style="text-align:left;">Status Approval</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal fade" id="detailTicketModal" tabindex="-1" aria-labelledby="detailTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-semibold" id="lbl_ticketno"></h5>
                    <input type="hidden" id="ticketno" name="ticketno">
                    <input type="hidden" id="jenis_ticket" name="jenis_ticket">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label id="label_deskripsi" class="form-label fw-semibold"></label>
                            <textarea class="form-control " rows="12" disabled id="deskripsi" style="resize:none;"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6"><label class="form-label fw-semibold">Nama</label><input
                                        type="text" class="form-control " id="nama" disabled></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Departemen</label><input
                                        type="text" class="form-control " id="departemen" disabled></div>
                            </div>
                            <div id="div_hardware" class="row g-3 mb-3">
                                <div class="col-md-6"><label class="form-label fw-semibold">Nama Hardware</label><input
                                        type="text" class="form-control " id="hardware_name" disabled></div>
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
                    <div class="mt-2"><label class="form-label fw-semibold">Remarks</label>
                        <textarea class="form-control" id="remarks" placeholder="Tulis remarks di sini..."
                            style="height:50px; max-width:300px; resize:none;"></textarea>
                    </div>
                    <div class="d-flex justify-content-end my-2">
                        <a href="javascript:void(0)" id="btnNg" class="btn btn-outline-danger me-2" onclick="btnNg()"><i
                                class="fa fa-times me-1"></i> NG</a>
                        <a href="javascript:void(0)" id="btnOk" class="btn btn-success" onclick="btnOk()"><i
                                class="fa fa-check me-1"></i>Ok</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        let table;
        $(document).ready(function() {

            table = $('#tabel').DataTable({
                processing: true,
                serverSide: false,
                scrollY: '400px',
                scrollX: true,
                scrollCollapse: true,

                columns: [{
                        data: 'tgl_permintaan',
                        width: "130px"
                    },
                    {
                        data: 'ticket_no',
                        width: "170px"
                    },
                    {
                        data: 'nama_lengkap',
                        width: "170px"
                    },
                    {
                        data: 'jenis_ticket',
                        width: "170px",
                        render: function(data) {
                            switch (data) {
                                case 'software':
                                    return 'Software';
                                case 'hardware':
                                    return 'Hardware';
                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'status_approval',
                        className: 'text-center',
                        width: "170px",
                        render: function(data) {
                            let badge = '';
                            switch (data) {
                                case 'waiting':
                                    badge =
                                        '<span class="badge bg-warning text-dark">Waiting</span>';
                                    break;
                                case 'approved':
                                    badge = '<span class="badge bg-success">Approved</span>';
                                    break;
                                case 'rejected':
                                    badge = '<span class="badge bg-danger">Rejected</span>';
                                    break;
                                default:
                                    badge = '<span class="badge bg-secondary">' + data + '</span>';
                                    break;
                            }
                            return badge;
                        }
                    }
                ],

                rowCallback: function(row, data) {
                    if (data.need_approve) {
                        $(row).addClass('table-danger');
                    } else {
                        $(row).removeClass('table-danger');
                    }
                }
            });

            // load data setelah DataTable siap
            loadTable();

            // double click row
            $('#tabel tbody').on('dblclick', 'tr', function() {
                let rowData = table.row(this).data();
                if (!rowData) return;

                $('#lbl_ticketno').text(rowData.ticket_no);
                $('#ticketno').val(rowData.ticket_no);
                $('#deskripsi').val(rowData.deskripsi ?? '');
                $('#nama').val(rowData.nama_lengkap ?? '');
                $('#departemen').val(rowData.nama_departemen ?? '');
                $('#hardware_name').val(rowData.nama_hardware ?? '');

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
                if (rowData.usercreate_confirm === '' || rowData.usercreate_confirm === null ) {
                    $('#btnOk').addClass('enable');
                    $('#btnNg').addClass('enable');
                } else{
                    $('#btnOk').addClass('disabled');
                    $('#btnNg').addClass('disabled');
                }
            });
        });


        function loadTable() {
            $.ajax({
                url: '/ticketings/data_user_confirm_hardware',
                type: "POST",
                dataType: "json",
                beforeSend: function() {
                    Swal.fire({
                        title: 'Load',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    table.clear().draw();
                },
                success: function(res) {
                    if (res.success) {
                        table.rows.add(res.data).draw();
                    } else {
                        alert(res.message ?? 'Gagal mengambil data');
                    }
                    swal.close();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    swal.close();
                    alert('Terjadi kesalahan saat mengambil data');
                }
            });
        }

        function btnOk() {
            submitApproval("ok");
        }

        function btnNg() {
            submitApproval("ng");
        }

        function submitApproval(status) {
            let ticket_no = $('#ticketno').val();
            let remarks = $('#remarks').val();
            console.log(ticket_no);
            console.log(remarks);
            console.log(status);
            if (!ticket_no) {
                Swal.fire("Error", "Ticket tidak terbaca!", "error");
                return;
            }
            $.ajax({
                url: '/ticketings/proses_user_confirm_hardware', // pastikan route benar di web.php
                type: "POST",
                data: {
                    ticket_no: ticket_no,
                    remarks: remarks,
                    status: status,
                },
                success: function(res) {
                    // console.log("Server Response:", res);
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        $('#detailTicketModal').modal('hide');
                        resetDetailTicket();
                        // applyFilter();
                    }, 2000);
                },
                error: function(xhr) {
                    // console.error("AJAX Error Object:", xhr);
                    // console.log("Status Code:", xhr.status);
                    // console.log("Response Text:", xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        console.log("Server Message:", xhr.responseJSON.message);
                    }
                    // Tampilkan alert Swal
                    let msg = "Terjadi kesalahan Server";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire("Gagal", msg, "error");
                }

            });
        }

        function resetDetailTicket() {
            $('#lbl_ticketno').text('');
            $('#ticketno').val('');
            $('#jenis_ticket').val('');
            $('#deskripsi').val('');
            $('#nama').val('');
            $('#departemen').val('');
            $('#hardware_name').val('');
            $('#remarks').val('');
            $('#label_deskripsi').text('');
            $('#lampiran_files').empty();

            $('#btnOk').show();
            $('#btnNg').show();
            $('#remarks').removeClass('is-invalid is-valid');
            if (table) {table.clear().draw();}
        }
    </script>
@endsection
