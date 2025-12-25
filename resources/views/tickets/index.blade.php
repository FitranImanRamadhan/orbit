@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <a href="javascript:void(0)" onclick="btnTambah()" class="btn btn-success">+ Tambah</a>
</div>

<div class="table-responsive">
    <table id="tabel" class="table table-hover table-bordered align-middle text-center" style="width:100%">
        <thead class="table-dark">
           <tr>
                <th>No</th>
                <th style="text-align:left;">Nama Ticket</th>
                <th style="text-align:left;">Tipe</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">
      
      <!-- Header -->
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold">
          Tambah Ticket
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <input type="hidden" id="id_ticket">

      <!-- Body -->
      <div class="modal-body bg-light">

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Nama Ticket</label>
          <input type="text" class="form-control shadow-sm" id="nama_ticket" 
                 placeholder="Contoh: Ticket Perbaikan Jaringan">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold text-secondary">Tipe Ticket</label>
            <select class="form-control shadow-sm" id="tipe">
                <option value="">-- Pilih Tipe Ticket --</option>
                <option value="hardware">Hardware</option>
                <option value="software">Software</option>
            </select>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          Batal
        </button>
        <button type="button" class="btn btn-primary px-4" onclick="btnSimpan()">
          Simpan
        </button>
      </div>

    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
let mode = "tambah";

$(document).ready(function() {
    $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/tickets/data',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nama_ticket', className: 'text-start' },
            { data: 'tipe', render: d => d ? d.charAt(0).toUpperCase() + d.slice(1) : ''},
            { data: null,
                render: () => `
                    <button class="btn btn-sm btn-warning me-1 btn-edit">Edit</button>
                    <button class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                `,
                orderable: false,
                searchable: false,
                className: "text-start"
            }
        ]
    });

    $('#tabel').on('click', '.btn-edit', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data();
        btnEdit(data);
    });

    $('#tabel').on('click', '.btn-hapus', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data();
        btnHapus(data.id_ticket);
    });
});

// ===============================
// BUTTON HAPUS
// ===============================
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
                url: `/tickets/delete/${id}`,
                type: 'DELETE',
                success: function(response){
                    $('#tabel').DataTable().ajax.reload();
                    Swal.fire('Berhasil', response.message, 'success');
                },
                error: function(){
                    Swal.fire('Error', 'Gagal menghapus ticket', 'error');
                }
            });
        }
    });
}

// ===============================
// BUTTON TAMBAH
// ===============================
function btnTambah() {
    mode = 'tambah';
    $('#id_ticket').val('');
    $('#nama_ticket').val('');
    $('#tipe').val('');
    $('#ticketModal').modal('show');
}

// ===============================
// BUTTON EDIT
// ===============================
function btnEdit(data) {
    mode = 'edit';
    $('#id_ticket').val(data.id_ticket);
    $('#nama_ticket').val(data.nama_ticket);
    $('#tipe').val(data.tipe);
    $('#ticketModal').modal('show');
}

// ===============================
// BUTTON SIMPAN
// ===============================
function btnSimpan() {
    let id = $('#id_ticket').val();
    let nama = $('#nama_ticket').val().trim();
    let tipe = $('#tipe').val().trim();

    if (nama === '') {
        Swal.fire('Peringatan', 'Nama ticket tidak boleh kosong', 'warning');
        return;
    }

    let url = (mode === 'tambah')
        ? '/tickets/create'
        : '/tickets/update/' + id;

    $.ajax({
        url: url,
        type: 'POST',
        data: { 
            id_ticket: id,
            nama_ticket: nama,
            tipe: tipe
        },
        success: function(response) {
            $('#ticketModal').modal('hide');
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
