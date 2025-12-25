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
                <th style="text-align:left;">Nama Software</th>
                <th style="text-align:left;">Keterangan</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="swmodal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">
      
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold">
         Tambah Software
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <input type="hidden" id="id_software">

      <div class="modal-body bg-light">

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Nama Software</label>
          <input type="text" class="form-control shadow-sm" id="nama_software" 
                 placeholder="Contoh: SAP, HRIS, Payroll">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Keterangan</label>
          <textarea class="form-control shadow-sm" id="keterangan" rows="3"
            placeholder="Isi keterangan software..."></textarea>
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
$(document).ready(function() {

    $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/softwares/data',
            type: 'POST',
            dataSrc: 'data'
        },
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        columns: [
            { data: null, render: (d,t,r,m)=> m.row + 1, className:'text-center' },
            { data: 'nama_software', className:'text-start' },
            { data: 'keterangan', className:'text-start' },
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
        btnHapus(data.id_software);
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
    }).then((result)=>{
        if(result.isConfirmed){
            $.ajax({
                url: `/softwares/delete/${id}`,
                type: 'DELETE',
                success: function(res){
                    $('#tabel').DataTable().ajax.reload();
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(){
                    Swal.fire('Error', 'Gagal menghapus software', 'error');
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
    $('#id_software').val('');
    $('#nama_software').val('');
    $('#keterangan').val('');
    $('#swmodal').modal('show');
}

// ===============================
// EDIT
// ===============================
function btnEdit(data) {
    mode = 'edit';
    $('#id_software').val(data.id_software);
    $('#nama_software').val(data.nama_software);
    $('#keterangan').val(data.keterangan);
    $('#swmodal').modal('show');
}

function setErrorFocus(selector) {
    $(selector)
        .css('border', '1px solid red')
        .focus()
        .one('input change keyup click', function () {
            $(this).css('border', '');
        });
}
// ===============================
// SIMPAN
// ===============================
function btnSimpan() {
    let id = $('#id_software').val();
    let nama = $('#nama_software').val().trim();
    let keterangan = $('#keterangan').val().trim();

    if (nama === '') {
        setErrorFocus('#nama_software');
        Swal.fire('Peringatan', 'Nama software tidak boleh kosong', 'warning');
        return;
    }

    let url = (mode === 'tambah')
                ? '/softwares/create'
                : '/softwares/update/' + id;
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id_software: id,
            nama_software: nama,
            keterangan: keterangan
        },
        success: function(res){
            $('#swmodal').modal('hide');
            $('#tabel').DataTable().ajax.reload();
            Swal.fire('Berhasil', res.message, 'success');
        },
        error: function(xhr){
            let err = xhr.responseJSON?.message || 'Gagal menyimpan';
            Swal.fire('Error', err, 'error');
        }
    });
}
</script>
@endsection
