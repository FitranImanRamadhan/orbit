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
                <th style="text-align:left;">Nama Plant</th>
                <th style="text-align:left;">Label</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="plantmodal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">

      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold">Tambah Plant</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <input type="hidden" id="id_plant">

      <div class="modal-body bg-light">

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Nama Plant</label>
          <input type="text" class="form-control shadow-sm" id="nama_plant"
                 placeholder="Masukkan nama plant...">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Label</label>
          <input type="text" class="form-control shadow-sm" id="label"
                 placeholder="Masukkan label plant (opsional)">
        </div>

      </div>

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
let mode = 'tambah';

$(document).ready(function() {

    $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/plants/data',
            type: 'POST',
            dataSrc: 'data'
        },
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        columns: [
            { data: null, render: (d,t,r,m)=> m.row + 1, className:'text-center' },
            { data: 'nama_plant', className:'text-start' },
            { data: 'label', className:'text-start' },
            { 
                data: null,
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
        btnHapus(data.id_plant);
    });
});


// ===============================
// HAPUS
// ===============================
function btnHapus(id) {
    Swal.fire({
        title: 'Yakin ingin hapus?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((r)=>{
        if(r.isConfirmed){
            $.ajax({
                url: `/plants/delete/${id}`,
                type: 'DELETE',
                success: function(res){
                    $('#tabel').DataTable().ajax.reload();
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(){
                    Swal.fire('Error', 'Gagal menghapus plant', 'error');
                }
            });
        }
    });
}


// ===============================
// TAMBAH
// ===============================
function btnTambah() {
    mode = 'tambah';
    $('#id_plant').val('');
    $('#nama_plant').val('');
    $('#label').val('');
    $('#plantmodal').modal('show');
}


// ===============================
// EDIT
// ===============================
function btnEdit(data) {
    mode = 'edit';
    $('#id_plant').val(data.id_plant);
    $('#nama_plant').val(data.nama_plant);
    $('#label').val(data.label);
    $('#plantmodal').modal('show');
}


// ===============================
// SIMPAN
// ===============================
function setErrorFocus(selector) {
    $(selector)
        .css('border', '1px solid red')
        .focus()
        .one('input change keyup click', function () {
            $(this).css('border', '');
        });
}

function btnSimpan() {
    let id = $('#id_plant').val();
    let nama = $('#nama_plant').val().trim();
    let label = $('#label').val().trim();

    if (nama === '') {
        etErrorFocus('#nama_plant');
        return Swal.fire('Peringatan', 'Nama plant tidak boleh kosong', 'warning');
    }

    let url = (mode === 'tambah') ? '/plants/create' : '/plants/update/' + id;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id_plant: id,
            nama_plant: nama,
            label: label
        },
        success: function(res) {
            $('#plantmodal').modal('hide');
            $('#tabel').DataTable().ajax.reload();
            Swal.fire('Berhasil', res.message, 'success');
        },
        error: function(xhr) {
            let err = xhr.responseJSON?.message || 'Gagal menyimpan data plant';
            Swal.fire('Error', err, 'error');
        }
    });
}
</script>
@endsection
