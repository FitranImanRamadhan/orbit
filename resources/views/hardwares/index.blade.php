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
                <th>No</th>
                <th>Nama Hardware</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="hwmodal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-3">
      
      <!-- Header -->
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold">
         Tambah Hardware
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <input type="hidden" id="id_hardware">

      <!-- Body -->
      <div class="modal-body bg-light">

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Nama Hardware</label>
          <input type="text" class="form-control shadow-sm" id="nama_hardware" 
                 placeholder="Contoh: Printer, Mouse, Monitor">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Kategori</label>
          <input type="text" class="form-control shadow-sm" id="kategori" 
                 placeholder="Contoh: Input, Output, Peripheral">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Keterangan</label>
          <textarea class="form-control shadow-sm" id="keterangan" rows="3"
            placeholder="Isi keterangan hardware..."></textarea>
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
let table;

$(document).ready(function () {
    table = $('#tabel').DataTable({
        ajax: {
            url: "{{ route('hardwares.data') }}",
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
            { data: 'nama_hardware', className: 'text-left' },
            { data: 'kategori', className: 'text-left' },
            { data: 'keterangan', className: 'text-left' },
            {
                data: null,
                render: () => `
                    <button class="btn btn-sm btn-warning btn-edit">Edit</button>
                    <button class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                `,
                orderable: false
            }
        ]
    });

    $('#tabel tbody').on('click', '.btn-edit', function () {
        let data = table.row($(this).parents('tr')).data();
        editHardware(data);
    });

    $('#tabel tbody').on('click', '.btn-hapus', function () {
        let data = table.row($(this).parents('tr')).data();
        hapusHardware(data.id_hardware);
    });
});

/* =============================
   TAMBAH
============================= */
function btnTambah() {
    $('#id_hardware').val('');
    $('#nama_hardware').val('');
    $('#kategori').val('');
    $('#keterangan').val('');
    $('#hwmodal').modal('show');
}

function setErrorFocus(selector) {
    $(selector)
        .css('border', '1px solid red')
        .focus()
        .one('input change keyup click', function () {
            $(this).css('border', '');
        });
}

/* =============================
   SIMPAN
============================= */
function btnSimpan() {
    let id = $('#id_hardware').val();
    let nama = $('#nama_hardware').val().trim();
    if (nama === '') {
        etErrorFocus('#nama_hardware');
        return Swal.fire('Peringatan', 'Nama Hardware tidak boleh kosong', 'warning');
    }

    let url = id
        ? "{{ url('hardwares/update') }}/" + id
        : "{{ route('hardwares.create') }}";

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id_hardware: id,
            nama_hardware: nama,
            kategori: $('#kategori').val(),
            keterangan: $('#keterangan').val()
        },
        success: function (res) {
            $('#hwmodal').modal('hide');

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            });

            table.ajax.reload(null, false);
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Proses gagal'
            });
        }
    });
}


/* =============================
   EDIT
============================= */
function editHardware(data) {
    $('#id_hardware').val(data.id_hardware);
    $('#nama_hardware').val(data.nama_hardware);
    $('#kategori').val(data.kategori);
    $('#keterangan').val(data.keterangan);
    $('#hwmodal').modal('show');
}

/* =============================
   HAPUS
============================= */
function hapusHardware(id) {
    Swal.fire({
        title: 'Hapus data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('hardwares/delete') }}/" + id,
                type: 'DELETE',
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: res.message,
                        timer: 1200,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak bisa menghapus data'
                    });
                }
            });
        }
    });
}
</script>
@endsection
