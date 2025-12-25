@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <a href="javascript:void(0)" onclick="btnTambah()" class="btn btn-success">+ Tambah</a>
</div>

<div class="table-responsive">
    <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-center" style="width:100%">
        <thead class="table-dark">
           <tr>
                <th style="text-align:center;">No</th>
                <th style="text-align:left;">Nama Departemen</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="deptmodal" tabindex="-1" aria-labelledby="deptmodalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">
      
      <!-- Header -->
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold" id="deptmodalLabel">
         Tambah Departemen
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Hidden input -->
      <input type="hidden" id="departemen_id" name="departemen_id">

      <!-- Body -->
      <div class="modal-body bg-light">
        <div class="mb-3">
          <label for="nama_departemen" class="form-label fw-semibold text-secondary">
             Nama Departemen
          </label>
          <input type="text" class="form-control shadow-sm" id="nama_departemen" name="nama_departemen"
                 placeholder="Masukkan nama departemen..." required>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Batal
        </button>
        <button type="button" class="btn btn-primary px-4" onclick="btnSimpan()">
          <i class="bi bi-save2 me-1"></i> Simpan
        </button>
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
            url: '/departemens/data',
            type: 'POST',
            dataSrc: 'data'
        },
        scrollY: '400px',        // scroll vertikal 400px
        scrollX: true,           // aktifkan scroll horizontal
        scrollCollapse: true,    // tabel akan mengecil kalau datanya sedikit
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
            { data: 'nama_departemen', name: 'nama_departemen', className: 'text-start' },           // Nama
            { data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning me-1" onclick="btnEdit(${row.id_departemen}, '${row.nama_departemen}')">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="btnHapus(${row.id_departemen})">Hapus</button>
                    `;
                },
                orderable: false,
                searchable: false,
                className: "text-start" // ← ini bikin rata kiri
            }
        ]
    });
});

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
                    url: `/departemens/${id}`,
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
        $('#nama_departemen').val(''); // reset input
        $('#deptmodal').modal('show'); // tampilkan modal
    }

    function btnEdit(id, nama) {
        $('#departemen_id').val(id);
        $('#nama_departemen').val(nama);
        $('#deptmodal').modal('show');
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
        let id_departemen = $('#departemen_id').val();
        let nama_departemen = $('#nama_departemen').val().trim();
        if (nama_departemen === '') {
            setErrorFocus('#nama_departemen');
            Swal.fire('Peringatan', 'Nama departemen tidak boleh kosong', 'warning');
            return;
        }
        let url = id_departemen ? '/departemens/update' : '/departemens/create';
        $.ajax({
            url: url,
            type: 'POST',
            data: {  id_departemen: id_departemen, nama_departemen: nama_departemen },
            success: function(response) {
                $('#deptmodal').modal('hide');
                $('#tabel').DataTable().ajax.reload();
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function(xhr) {
                let err = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan';
                Swal.fire('Error', err, 'error');
            }
        });
    }

</script>
@endsection
