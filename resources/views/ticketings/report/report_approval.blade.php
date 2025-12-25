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
                    <th style="text-align:left;">Week</th>
                    <th style="text-align:left;">Jenis Ticket</th>
                    <th style="text-align:left;">Status Approval</th>
                    <th style="text-align:left;">Download Excel</th>
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
                            return '<button class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i></button>';
                        }
                    }
                ]
            });
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
                success: function (res) {
                    if (!res.success) {
                        table.clear().draw();
                        alert(res.message);
                        return;
                    }
                    table.clear().rows.add(res.data).draw();
                },
                error: function (xhr) {
                    alert('Gagal mengambil data');
                    console.error(xhr.responseText);
                }
            });
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
