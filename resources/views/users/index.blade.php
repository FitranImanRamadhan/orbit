@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <a href="javascript:void(0)" onclick="btnTambah()" class="btn btn-success">+ Tambah</a>
    <input type="hidden" id="login_user_akses" value="{{ auth()->user()->user_akses }}">
</div>

<div>
    <table id="tabel" class="table table-sm table-hover table-bordered align-middle text-center">
        <thead class="table-dark">
           <tr>
                <th style="text-align:center;">No</th>
                <th style="text-align:center;">NIK</th>
                <th style="text-align:center;">Nama Lengkap</th>
                <th style="text-align:center;">Plant</th>
                <th style="text-align:center;">Departemen</th>
                <th style="text-align:center;">Position</th>
                <th style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="modal fade" id="usermodal" tabindex="-1" aria-labelledby="usermodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary bg-gradient text-white border-0">
                <h6 class="modal-title fw-semibold" id="usermodalLabel">Tambah User</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light rounded-bottom-3">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white fw-semibold text-secondary py-2"> Daftar User</div>
                            <div class="card-body p-2" style="max-height: 430px; overflow-y: auto;">
                                <div class="table-responsive">
                                    <table id="tabel-dblink" class="table table-sm table-bordered table-striped table-hover table-sm mb-0"  style="font-size: 12px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>NIK</th>
                                                <th>Nama Lengkap</th>
                                                <th>Departemen</th>
                                                <th>Position</th>
                                                <th>Username</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm shadow-sm" id="id_user" name="id_user" hidden>
                                <div class="mb-3">
                                    <label for="nik" class="form-label fw-semibold text-secondary">NIK</label>
                                    <input type="text" class="form-control form-control-sm shadow-sm" id="nik" name="nik" placeholder="Masukkan NIK..."  required>
                                </div>
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label fw-semibold text-secondary">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-sm shadow-sm" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap..." required>
                                </div>
                                <div class="mb-3">
                                    <label for="user_akses" class="form-label fw-semibold text-secondary">User Akses</label>
                                    <select class="form-control form-control-sm shadow-sm" id="user_akses" name="user_akses" required>
                                        <option value="" selected disabled>-- Pilih Akses --</option>
                                        <option value="user">User</option>
                                        <option value="super_user">Super User</option>
                                    </select>
                                </div>
                                <hr class="my-2">
                                <h6 class="fw-bold text-secondary mb-2"> <i class="bi bi-person-circle me-1"></i> User Login </h6>
                                <small id="label_pw1" class="text-muted fst-italic d-block mb-2" style="font-size: 11px;"> Kosongkan jika tidak ingin ubah password</small>
                                <button type="button" id="ubah_pw" onclick="btnpw()" class="btn btn-warning btn-sm rounded-pill shadow-sm"><i class="bi bi-key-fill me-1"></i>Password</button>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="plant_id" class="form-label fw-semibold text-secondary">Plant</label>
                                    <select class="form-select form-select-sm shadow-sm" id="plant_id" name="plant_id" required style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                                        <option value="">-- Pilih Departemen --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="departemen_id" class="form-label fw-semibold text-secondary">Departemen</label>
                                    <select class="form-select form-select-sm shadow-sm" id="departemen_id" name="departemen_id" required style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                                        <option value="">-- Pilih Departemen --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="position_id" class="form-label fw-semibold text-secondary">Position</label>
                                    <select class="form-select form-select-sm shadow-sm" id="position_id" name="position_id" required style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                                        <option value="">-- Pilih Position --</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="div_username">
                                    <label for="username" class="form-label fw-semibold text-secondary">Username</label>
                                    <input type="text" class="form-control form-control-sm shadow-sm" id="username" name="username" placeholder="Masukkan username..." required>
                                    <input type="hidden" id="original_username" value="">
                                </div>
                                <hr class="my-2">
                                <div class="mb-2">
                                    <label for="password" class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Password</label>
                                    <small id="label_pw2" class="text-muted fst-italic d-block mb-1" style="font-size: 11px;">Kosongkan jika tidak ingin ubah password</small>
                                    <input type="password" class="form-control form-control-sm shadow-sm py-1 px-2" id="password" name="password" placeholder="Masukkan password..." style="font-size: 12px; height: 28px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                <button type="button" class="btn btn-primary px-4" onclick="btnSimpan()"><i class="bi bi-save2 me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let mode = '';
    let nama_departemen = "{{ Auth::user()->nama_departemen }}";
    let user_akses = "{{ Auth::user()->user_akses }}";

// // Cek kondisi
// if (user_akses === 'administrator') {
//     $('#div_username').show(); // tampilkan
// } else {
//     $('#div_username').hide(); // sembunyikan
// }
    $('#tabel').DataTable({
        processing: true,
        serverSide: false,
        ajax: { url: '/users/data', type: 'POST', dataSrc: 'data' },
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
            { data: 'nik', name: 'nik', className: 'text-start' },
            { data: 'nama_lengkap', name: 'nama_lengkap', className: 'text-start' },
            { data: 'nama_plant', name: 'nama_plant', className: 'text-start' },
            { data: 'nama_departemen', name: 'nama_departemen', className: 'text-start' },
            { data: 'nama_position', name: 'nama_position', className: 'text-start' },
            { data: null, render: function(data, type, row) {
                        return `
                          <button class="btn btn-sm btn-warning me-1 btn-edit">Edit</button>
                          <button class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                        `;
                  },orderable: false,
                    searchable: false,
                    className: "text-left",
                    width: "110px"
            }
        ]
    });

    $('#tabel').on('click', '.btn-edit', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data(); // ambil data baris
        btnEdit(data); // panggil modal edit
    });

    $('#tabel').on('click', '.btn-hapus', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data();
        btnHapus(data.id_user);
    });

    let table = $('#tabel-dblink').DataTable({
        processing: true,
        serverSide: false,
        ajax: {url: '/users/data_dblink', type: 'POST', dataSrc: 'data'},
        scrollY: '400px',
        scrollX: false,
        scrollCollapse: true,
        pageLength: 5,
        lengthMenu: [5, 10, 20],
        classes: {
            sWrapper: "dataTables_wrapper dt-bootstrap5 no-footer"
        },
        columns: [
            { data: 'nik', className: 'text-start' },
            { data: 'nama_lengkap', className: 'text-start' },
            { data: 'nama_departemen', className: 'text-start' },
            { data: 'nama_position', className: 'text-start' },
            { data: 'username', className: 'text-start' }
        ]
    });
    // ==== FUNGSI DOUBLE CLICK ====
    $('#tabel-dblink tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();
        if (!data) return; 
        $('#nik').val(data.nik);
        $('#nama_lengkap').val(data.nama_lengkap);
        $('#departemen_id').val(data.id_department);
        loadSect(data.id_department, function() {
            $('#position_id').val(data.id_position);
        });
        $('#username').val(data.username);
        console.log("Double click data:", data);
    });
});

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
                url: `/users/delete/${id}`,
                type: 'DELETE',
                success: function(res){
                    $('#tabel').DataTable().ajax.reload();
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function(){
                   cc
                }
            });
        }
    });
}

function btnTambah() {
    mode = 'tambah';
    $('#usermodalLabel').text('Tambah User');
    $('#nik').prop('readonly', false);
    $('#nik').val('');
    $('#nama_lengkap').val('');
    $('#username').val('');
    $('#password').val('');
    $('#password').prop('disabled', true);
    $('#departemen_id').val('');
    $('#position_id').val('');
    $('#user_akses').val('');
    $('#label_pw1').text('');
    $('#label_pw2').text('');
    loadPlant();
    loadDept();
    $('#usermodal').modal('show');
}

function btnEdit(data) {
    mode = 'edit';
    const loginAkses = $('#login_user_akses').val().toLowerCase();      
    const targetAkses = data.user_akses; 
    console.log(targetAkses);                
    if (targetAkses === 'administrator') {
        Swal.fire('Tidak bisa edit!', 'Administrator tidak boleh diedit.', 'warning');
        return;
    }
    if (loginAkses === 'user') {
        if (targetAkses === 'super_user' || targetAkses === 'administrator') {
            Swal.fire('Akses ditolak!', 'User tidak boleh mengedit role ini.', 'warning');
            return;
        }
    }
    if (loginAkses === 'super user' && targetAkses === 'administrator') {
        Swal.fire('Akses ditolak!', 'Super User tidak boleh mengedit Administrator.', 'warning');
        return;
    }
    $('#usermodalLabel').text('Edit User');
    $('#nik').prop('readonly', true);
    $('#usermodal').modal('show');
    $('#id_user').prop('readonly', true);
    $('#id_user').val(data.id_user);
    $('#nik').val(data.nik);
    $('#nama_lengkap').val(data.nama_lengkap);

    $('#div_username').hide();
    $('#username').val(data.username);
    $('#original_username').val(data.username);
    $('#username').val(data.username).prop('readonly', true);
    $('#original_username').val(data.username);

    $('#password').val('');
    $('#password').prop('disabled', true);
    $('#label_pw1').text('Kosongkan jika tidak ingin ubah password');
    $('#label_pw2').text('Kosongkan jika tidak ingin ubah password');
    loadPlant(function () {
        $('#plant_id').val(data.plant_id)
    });
    loadDept(function() {
        $('#departemen_id').val(data.departemen_id);
        loadSect(data.departemen_id, function() {
            $('#position_id').val(data.position_id);
        });
    });
    let akses = data.user_akses;
    $('#user_akses').val(akses);
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
    var id_user = $('#id_user').val();
    var nik = $('#nik').val();
    var nama_lengkap = $('#nama_lengkap').val();
    var plant_id = $('#plant_id').val();
    var departemen_id = $('#departemen_id').val();
    var position_id = $('#position_id').val();
    var username = $('#username').val();
    var password = $('#password').val();
    var user_akses = $('#user_akses').val();
    var originalUsername = $('#original_username').val(); // ambil username lama

    if (!nik)           return setErrorFocus('#nik'), Swal.fire('Peringatan', 'NIK wajib diisi', 'warning');
    if (!nama_lengkap)  return setErrorFocus('#nama_lengkap'), Swal.fire('Peringatan', 'Nama lengkap wajib diisi', 'warning');
    if (!plant_id)      return setErrorFocus('#plant_id'), Swal.fire('Peringatan', 'Plant wajib diisi', 'warning');
    if (!departemen_id) return setErrorFocus('#departemen_id'), Swal.fire('Peringatan', 'Departemen wajib diisi', 'warning');
    if (!position_id)   return setErrorFocus('#position_id'), Swal.fire('Peringatan', 'Position wajib diisi', 'warning');
    if (!username)      return setErrorFocus('#username'), Swal.fire('Peringatan', 'Username wajib diisi', 'warning');
    if (!user_akses)    return setErrorFocus('#user_akses'), Swal.fire('Peringatan', 'User akses wajib dipilih', 'warning');
    if (!id_user && !password) return setErrorFocus('#password'), Swal.fire('Peringatan', 'Password wajib diisi', 'warning');


    // === Peringatan jika username diubah ===
    if (mode === 'edit' && username !== originalUsername) {
        if (!confirm('Username diubah! Pastikan perubahan ini tidak mengganggu data ticket atau hirarki. Lanjutkan?')) {
            return; // batalkan simpan
        }
    }
    var userdata = {
        id_user: id_user,
        nik: nik,
        nama_lengkap: nama_lengkap,
        plant_id: plant_id,
        departemen_id: departemen_id,
        position_id: position_id,
        username: username,
        password: password,
        user_akses: user_akses
    };
    let url = '';
    if (mode === 'tambah') {
        if (!password) {Swal.fire('Peringatan', 'Password wajib diisi untuk user baru', 'warning'); return;}
        url = '/users/create';
    } else {
        url = '/users/update/' + id_user;
    }
    $.ajax({
        url: url,
        type: 'POST',
        data: userdata,
        success: function(response) {
            $('#usermodal').modal('hide');
            $('#tabel').DataTable().ajax.reload();
            Swal.fire('Berhasil', response.message, 'success');
        },
        error: function(xhr) {
            let err = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan';
            Swal.fire('Error', err, 'error');
        }
    });
}

function loadPlant(callback) {
    $.ajax({
        url: '/users/loadPlant',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            let dropdown = $('#plant_id');
            dropdown.empty();
            dropdown.append('<option value="">-- Pilih Plant --</option>');
            $.each(res.data, function(i, plant) {
                dropdown.append(`<option value="${plant.id_plant}">${plant.nama_plant}</option>`);
            });
            if (callback) callback();
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data plant', 'error');
        }
      });
    }


  function loadDept(callback) {
    $.ajax({
        url: '/users/loadDept',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            let dropdown = $('#departemen_id');
            dropdown.empty();
            dropdown.append('<option value="">-- Pilih Departemen --</option>');
            $.each(res.data, function(i, dept) {
                dropdown.append(`<option value="${dept.id_departemen}">${dept.nama_departemen}</option>`);
            });
            if (callback) callback();
            $('#departemen_id').off('change') // hapus event lama dulu
                .on('change', function() {
                    let dept_id = $(this).val();
                    if (dept_id) {
                      loadSect(dept_id);
                    } else {
                        $('#position_id').empty().append('<option value="">-- Pilih Position --</option>');
                    }
                });
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data departemen', 'error');
        }
      });
    }

  function loadSect(dept_id, callback) {
    // console.log(dept_id);
    $.ajax({
        url: '/users/loadSect',
        type: 'POST',
        data: { departemen_id: dept_id },
        dataType: 'json',
        success: function(res) {
            let dropdown = $('#position_id');
            dropdown.empty();
            dropdown.append('<option value="">-- Pilih Position --</option>');
            $.each(res.data, function(i, sect) {
                dropdown.append(`<option value="${sect.id_position}">${sect.nama_position}</option>`);
            });

            if (callback) callback();
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data position', 'error');
        }
      });
    }

    function btnpw() {
        const loginAkses = $('#login_user_akses').val().toLowerCase();
        if (mode === 'edit' && loginAkses !== 'administrator') {
            Swal.fire('Akses ditolak', 'Hanya Administrator yang bisa mengganti password.', 'error');
            return;
        }

        $('#password').prop('disabled', false).focus();
    }


 
</script>
@endsection
