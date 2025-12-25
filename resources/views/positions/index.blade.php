@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <a href="javascript:void(0)" onclick="btnTambah()" class="btn btn-success">+ Tambah</a>
</div>

<div class="table-responsive">
    <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-left" style="width:100%">
        <thead class="table-dark">
           <tr>
                <th style="text-align:center;">No</th>
                <th style="text-align:left;">Departemen</th>
                <th style="text-align:left;">Position</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="sectmodal" tabindex="-1" aria-labelledby="sectmodalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold" id="sectmodalLabel"> Tambah Position</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <input type="hidden" id="id_position" name="id_position">
      <div class="modal-body bg-light">
        <div class="mb-3">
          <label for="departemen_id" class="form-label fw-semibold text-secondary">Departemen</label>
          <select class="form-select shadow-sm" id="departemen_id" name="departemen_id"  required style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
            <option value="">-- Pilih Departemen --</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="nama_position" class="form-label fw-semibold text-secondary"> Nama Position</label>
          <input type="text" class="form-control shadow-sm" id="nama_position" name="nama_position" placeholder="Masukkan nama position..." required>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"> <i class="bi bi-x-circle me-1"></i> Batal</button>
        <button type="button" class="btn btn-primary px-4" onclick="btnSimpan()"><i class="bi bi-save2 me-1"></i> Simpan</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/positions/data',
            type: 'POST',
            dataSrc: 'data'
        },
        scrollY: '400px',        // scroll vertikal 400px
        scrollX: true,           // aktifkan scroll horizontal
        scrollCollapse: true,    // tabel akan mengecil kalau datanya sedikit
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' }, // No
            { data: 'nama_departemen', name: 'nama_departemen', className: 'text-start' },
            { data: 'nama_position', name: 'nama_position', className: 'text-start' },           // Nama
            { data: null, render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning me-1" 
                                onclick="btnEdit(${row.id_position}, 
                                                '${row.nama_position}', 
                                                ${row.departemen_id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="btnHapus(${row.id_position})">Hapus</button>
                    `;
                },
                orderable: false,
                searchable: false,
                className: "text-start" // ← ini bikin rata kiri
            }
        ]
    });
});

    function loadDept(callback) {
        $.ajax({
            url: '/positions/loadDept',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                let dropdown = $('#departemen_id');
                dropdown.empty();
                dropdown.append('<option value="">-- Pilih Departemen --</option>');

                $.each(res.data, function(i, dept) {
                    dropdown.append(`<option value="${dept.id_departemen}">${dept.nama_departemen}</option>`);
                })
                if (callback) callback();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire('Error', 'Gagal memuat data departemen', 'error');
            }
        });
    }

    function setErrorFocus(selector) {
            $(selector)
                .css('border', '1px solid red')
                .focus()
                .one('input change keyup click', function () {
                    $(this).css('border', '');
                });
        }
    
    function btnSimpan() {
        let id_position = $('#id_position').val();
        let departemen_id = $('#departemen_id').val();
        let nama_position = $('#nama_position').val().trim();
        if (!departemen_id) {
            setErrorFocus('#departemen_id');
            return Swal.fire('Peringatan', 'Departemen wajib diisi', 'warning');
        }
        if (!nama_position) {
            setErrorFocus('#nama_position');
            return Swal.fire('Peringatan', 'Nama Position wajib diisi', 'warning');
        }
        let url = id_position ? '/positions/update' : '/positions/create';
        $.ajax({
            url: url,
            type: 'POST',
            data: {  id_position: id_position, departemen_id: departemen_id, nama_position: nama_position },
            success: function(response) {
                $('#sectmodal').modal('hide');
                $('#tabel').DataTable().ajax.reload();
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function(xhr) {
                let err = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan';
                Swal.fire('Error', err, 'error');
            }
        });
    }


    function btnHapus(id) {
        Swal.fire({
            title: 'Yakin ingin hapus?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: `/positions/${id}`,
                    type: 'DELETE',
                    success: function(response){
                        $('#tabel').DataTable().ajax.reload();
                        Swal.fire('Berhasil', response.message, 'success');
                    },
                    error: function(){
                        Swal.fire('Error', 'Gagal menghapus departemen', 'error');
                    }
                });
            }
        });
    }

    function btnTambah() {
        $('#id_position').val('');
        $('#nama_position').val('');
        loadDept();
        $('#departemen_id').val('');
        $('#sectmodal').modal('show');
    }


    function btnEdit(id_position, nama_position, departemen_id) {
        $('#departemen_id').val('');
        $('#sectmodal').modal('show');
        $('#id_position').val(id_position);
        $('#nama_position').val(nama_position);
        loadDept(function() {
            $('#departemen_id').val(departemen_id);
        });
    }

</script>
@endsection
