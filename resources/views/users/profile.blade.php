@extends('layout')

@section('content')
<div class="row g-4">
    <!-- Sidebar Avatar & Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <div class="position-relative d-inline-block mb-3">
                    <div class="mb-3 text-center">
                        <img src="{{ asset(Auth::user()->avatar ? 'assets/img/avatar/' . Auth::user()->avatar : 'assets/img/avatar/default-avatar.png') }}"
                         id="avatar" width="140" height="140" class="rounded-circle">
                    </div>

                </div>
                <h4 class="fw-bold mb-1" id="nama_lengkap"></h4>
                <p class="text-muted mb-0" id="user_akses"></p>
            </div>
        </div>
    </div>

    <!-- Detail Profile Card -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Detail Profile</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </button>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">NIK</div>
                    <div class="col-sm-8" id="nik"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">Nama Lengkap</div>
                    <div class="col-sm-8" id="detail_nama"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">Plant</div>
                    <div class="col-sm-8" id="plant"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">Departemen</div>
                    <div class="col-sm-8" id="departemen"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">Position</div>
                    <div class="col-sm-8" id="position"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-secondary fw-medium">Username</div>
                    <div class="col-sm-8" id="username"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold" id="editProfileLabel">Edit Profile</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formProfile" class="row g-3">
            <div class="col-md-6">
                <label for="form_nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" class="form-control shadow-sm" id="form_nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="col-md-6" hidden>
                <label for="form_username" class="form-label fw-semibold">Username</label>
                <input type="text" class="form-control shadow-sm" id="form_username" name="username" required>
            </div>
            <div class="col-md-6">
                <label for="form_password" class="form-label fw-semibold">Password <small class="text-muted">(kosongkan jika tidak ingin diubah)</small></label>
                <input type="password" class="form-control shadow-sm" id="form_password" name="password">
            </div>
            <div class="col-md-6">
                <label for="form_avatar" class="form-label fw-semibold">Avatar</label>
                <input type="file" class="form-control shadow-sm" id="form_avatar" name="avatar" accept="image/*">
            </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="updateProfile()">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script>
    
$(document).ready(function() {
    $.ajax({
        url: '/users/profile/data',
        type: 'POST',
        data: {},
        success: function(res) {
            if(res.success){
                const u = res.data;
                $('#nama_lengkap').text(u.nama_lengkap);
                $('#user_akses').text(formatAkses(u.user_akses));
                $('#nik').text(u.nik);
                $('#detail_nama').text(u.nama_lengkap);
                $('#plant').text(u.plant ?? '-');
                $('#departemen').text(u.departemen ?? '-');
                $('#position').text(u.position ?? '-');
                $('#username').text(u.username);

                // Isi form modal edit
                $('#form_nama_lengkap').val(u.nama_lengkap);
                $('#form_username').val(u.username);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Gagal mengambil data profile', 'error');
        }
    });
});


// Fungsi format label user_akses
function formatAkses(role) {
    if(role === 'administrator') return 'Administrator';
    if(role === 'super_user') return 'Super User';
    return 'User';
}

// Update profile via AJAX
function updateProfile() {
    let nama_lengkap = $('#form_nama_lengkap').val();
    let username     = $('#form_username').val();
    let password     = $('#form_password').val();
    let avatarFile   = $('#form_avatar')[0].files[0]; // ambil file avatar jika ada
    
    let formData = new FormData();
    formData.append('nama_lengkap', nama_lengkap);
    formData.append('username', username);
    formData.append('password', password || ''); // kosongkan jika tidak diubah

    formData.append('avatar', $('#form_avatar')[0].files[0]); // file avatar

    $.ajax({
        url: '/users/profile/update',
        type: 'POST',
        data: formData,
        contentType: false,      // WAJIB
        processData: false,      // WAJIB
        cache: false,
        success: function(res) {
                $('#editProfileModal').modal('hide');
                Swal.fire('Berhasil', 'Profile berhasil diperbarui', 'success').then(() => location.reload());
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memperbarui profile';
                Swal.fire('Error', msg, 'error');
            }
        });
}
</script>
@endsection
