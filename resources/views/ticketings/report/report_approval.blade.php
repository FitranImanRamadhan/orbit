@extends('layout')

@section('content')
    <div class="row g-3 justify-content-start mb-3 align-items-end">
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
            <button class="btn btn-secondary" onclick="resetFilter()"><i
                    class="bi bi-arrow-clockwise me-1"></i>ResetForm</button>
            <button class="btn btn-primary" onclick="applyFilter()"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th style="text-align:left;">Tanggal Permintaan</th>
                    <th style="text-align:left;">Year</th>
                    <th style="text-align:left;">Month</th>
                    <th style="text-align:left;">Week</th>
                    <th style="text-align:left;">Jenis Ticket</th>
                    <th style="text-align:left;">Status Approval</th>
                    <th style="text-align:left;">Download Excel</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="modal fade" id="detailTicketModal" tabindex="-1" aria-labelledby="detailTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-semibold mb-0">
                        Approval Ticket :&nbsp;
                    </h5>
                    <small class="fw-semibold">
                        <span id="label_approve"></span>
                    </small>
                    <input type="text" id="ticketno" hidden>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="table-responsive">
                        <table id="tabel2" class="table table-sm table-hover table-bordered align-middle text-left"
                            style="width:100%">
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
                    <hr>

                    <div class="row g-2 mb-2">
                        <div class="row g-2 mb-2">

                            <!-- Department Tickets -->
                            <div class="col-md-6">
                                <div class="card border" style="height: 250px;">
                                    <div class="card-body p-1" style="height:100%; position:relative;">
                                        <div class="small fw-semibold text-center mb-1">
                                            Department Tickets
                                        </div>

                                        <div style="height: 220px;">
                                            <canvas id="problemChartPie"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Jenis Problem -->
                            <div class="col-md-6">
                                <div class="card border" style="height: 250px;">
                                    <div class="card-body p-1" style="height:100%; position:relative;">
                                        <div class="small fw-semibold text-center mb-1">
                                            Jenis Problem
                                        </div>

                                        <div style="height: 220px;">
                                            <canvas id="problemChartDoughnut"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end my-2">
                        <a href="javascript:void(0)" id="btnNotApproved" class="btn btn-outline-danger me-2"
                            onclick="btnNotApproved()"><i class="fa fa-times me-1"></i> Not Approved</a>
                        <a href="javascript:void(0)" id="btnApproved" class="btn btn-success" onclick="btnApproved()"><i
                                class="fa fa-check me-1"></i> Approved</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let table;
        let table2;
        let doughnutChart, pieChart;
        let currentUser = "{{ Auth::user()->username }}";

        $(document).ready(function() {
            table = $('#tabel').DataTable({
                processing: true,
                serverSide: false,
                destroy: true,
                scrollY: '400px',
                scrollX: true,
                scrollCollapse: true,
                data: [],
                columns: [{
                        data: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'year',
                        className: 'text-center'
                    },
                    {
                        data: 'month',
                        className: 'text-center'
                    },
                    {
                        data: 'week',
                        className: 'text-center'
                    },
                    {
                        data: 'jenis_ticket',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1);
                        }
                    },
                    {
                        data: 'status_approval',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) {
                                return '<span class="badge bg-warning text-dark">Waiting</span>';
                            }
                            if (data === 'approved') {
                                return '<span class="badge bg-success">Approved</span>';
                            }
                            if (data === 'rejected') {
                                return '<span class="badge bg-danger">Rejected</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function() {
                            return `<a href="javascript:void(0)" class="btn btn-sm btn-success" onclick="exportExcel(this)"> <i class="bi bi-file-earmark-excel"></i></a>`;
                        }
                    }
                ]
            });

            const table2 = $('#tabel2').DataTable({
                processing: true,
                serverSide: false,
                scrollY: '400px',
                scrollX: true,
                scrollCollapse: true,
                searching: false,
                ordering: true,
                columns: [{
                        data: 'nama_departemen'
                    },
                    {
                        data: 'manpower',
                        className: 'text-center'
                    },
                    {
                        data: 'hardware',
                        className: 'text-center'
                    },
                    {
                        data: 'network',
                        className: 'text-center'
                    },
                    {
                        data: 'software',
                        className: 'text-center'
                    },
                    {
                        data: 'solved',
                        className: 'text-center'
                    },
                    {
                        data: 'unsolved',
                        className: 'text-center'
                    },
                    {
                        data: 'total',
                        className: 'text-center fw-semibold'
                    }
                ]
            });


            $('#tabel tbody').on('dblclick', 'tr', function() {
                var rowData = table.row(this).data(); // ambil data baris yang di-dblclick
                // Ambil nilai kolom yang diinginkan
                var year = rowData.year;
                var month = rowData.month;
                var week = rowData.week;
                var jenis_ticket = rowData.jenis_ticket;

                var labelJenis = '';
                if (jenis_ticket === 'hardware') {
                    labelJenis = 'Hardware';
                } else if (jenis_ticket === 'software') {
                    labelJenis = 'Software';
                } else {
                    labelJenis = jenis_ticket; // fallback
                }
                var labelApprove = labelJenis + ' | ' + year + ' - ' + month + ' (Week ' + week + ')';
                $('#label_approve').text(labelApprove);

                var ajaxUrl = '';
                if (jenis_ticket.toLowerCase() === 'hardware') {
                    ajaxUrl = '/ticketings/report/data_report_ticket_hardware';
                } else if (jenis_ticket.toLowerCase() === 'software') {
                    ajaxUrl = '/ticketings/report/data_report_ticket_software';
                } else {
                    Swal.fire('Error', 'Jenis ticket tidak dikenal: ' + jenis_ticket, 'error');
                    return;
                }
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        year: year,
                        month: month,
                        week: week
                    },
                    success: function(res) {
                        if (res.success) {
                            table2.clear().rows.add(res.data).draw();
                            // 👉 ISI FOOTER DARI BACKEND
                            $('#sum_manpower').text(res.sum_totals.sum_manpower);
                            $('#sum_hardware').text(res.sum_totals.sum_hardware);
                            $('#sum_network').text(res.sum_totals.sum_network);
                            $('#sum_software').text(res.sum_totals.sum_software);

                            loadDiagram(year, month, week, jenis_ticket);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                        $('#detailTicketModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal mengambil data', 'error');
                    }
                });
                const btnApprove = $('#btnApproved');
                const btnReject = $('#btnNotApproved');

                // RESET STATE (WAJIB)
                $('#btnApproved').hide();
                $('#btnNotApproved').hide();
                console.log('currentUser:', currentUser);
                console.log('rowData.approver_level2:', rowData.approver_level2);
                console.log('rowData.status_level2:', rowData.status_level2);
                let hideButtons = true;
                if (jenis_ticket === 'software' || jenis_ticket === 'hardware') {
                    if (currentUser === rowData.approver_level2 && rowData.status_level2 == null) {
                        hideButtons = false;
                    }
                    if (currentUser === rowData.approver_level3 && rowData.status_level3 == null) {
                        hideButtons = false;
                    }
                }

                // TAMPILKAN JIKA BOLEH
                if (!hideButtons) {
                    $('#btnApproved').show();
                    $('#btnNotApproved').show();
                }
            });
            $('#detailTicketModal').on('hidden.bs.modal', function() {});

            // --- Show/hide tombol Approve/Reject berdasarkan status ---


        });



        function applyFilter() {
            if (!$('#filter_status_approval').val()) {
                setErrorFocus('#filter_status_approval');
                return;
            }
            loadData();
        }

        function loadData() {
            $.ajax({
                url: '/ticketings/report/data_report_approval',
                type: 'post',
                data: {
                    status_approval: $('#filter_status_approval').val()
                },
                success: function(res) {
                    if (!res.success) {
                        table.clear().draw();
                        Swal.fire('Warning', res.message, 'warning');
                        return;
                    }
                    table.clear().rows.add(res.data).draw();
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Terjadi Kesalahan Server', 'error');
                    console.error(xhr.responseText);
                }
            });
        }

        // Fungsi loadDiagram
        function loadDiagram(year, month, week, jenis_ticket) {
            let chartUrl = '';

            if (jenis_ticket.toLowerCase() === 'hardware') {
                chartUrl = '/ticketings/report/data_chart_ticket_hardware';
            } else if (jenis_ticket.toLowerCase() === 'software') {
                chartUrl = '/ticketings/report/data_chart_ticket_software';
            } else {
                console.error('Jenis ticket tidak dikenal:', jenis_ticket);
                return;
            }

            $.ajax({
                url: chartUrl,
                type: 'POST',
                data: {
                    year,
                    month,
                    week
                },
                success: function(res) {
                    if (res.success) {
                        renderDonut(res.donut);
                        renderPie(res.pie);
                    }
                },
                error: function() {
                    console.error('Gagal load diagram');
                }
            });
        }

        const problemChartDoughnut = document.getElementById('problemChartDoughnut');
        const problemChartPie = document.getElementById('problemChartPie');

        function renderPie(pie) {
            const labels = pie.map(dept => dept.nama_departemen);
            const data = pie.map(dept => dept.total);

            if (pieChart) pieChart.destroy();

            pieChart = new Chart(problemChartPie, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#0d6efd',
                            '#20c997',
                            '#ffc107',
                            '#dc3545'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // 🔥 WAJIB agar ikut height canvas
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom', // legend di bawah → chart makin kecil
                            labels: {
                                boxWidth: 10,
                                padding: 8,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }


        function renderDonut(donut) {
            const labels = ['Manpower', 'Hardware', 'Network', 'Software'];
            const data = [
                donut.sum_manpower ?? 0,
                donut.sum_hardware ?? 0,
                donut.sum_network ?? 0,
                donut.sum_software ?? 0
            ];
            if (doughnutChart) doughnutChart.destroy();
            doughnutChart = new Chart(problemChartDoughnut, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#0d6efd', '#dc3545', '#198754', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 10
                                },
                                boxWidth: 10,
                                padding: 8
                            }
                        }
                    }
                }

            });
        }

        function exportExcel(btn) {
            const rowData = table.row($(btn).closest('tr')).data();

            if (!rowData) {
                Swal.fire('Error', 'Data tidak ditemukan', 'error');
                return;
            }

            const {
                year,
                month,
                week,
                jenis_ticket
            } = rowData;

            const params = $.param({
                year,
                month,
                week,
                jenis_ticket
            });
            console.log(params);
            window.location.href = '/ticketings/report/export_excel?' + params;
        }




        function resetFilter() {
            $('#filter_status_approval').val('');
            loadData();
        }

        function setErrorFocus(selector) {
            $(selector)
                .css('border', '1px solid red')
                .focus()
                .one('change keyup click', function() {
                    $(this).css('border', '');
                });
        }
    </script>
@endsection
