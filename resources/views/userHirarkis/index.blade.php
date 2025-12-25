@extends('layout')

@section('content')
<style>
    .ui-autocomplete {
    max-height: 130px !important;
    overflow-y: auto !important;
    z-index: 9999 !important;
}

</style>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <a href="javascript:void(0)" onclick="btnTambahHirarki()" class="btn btn-success">+ Tambah</a>
</div>

<div>
    <table id="tabel" class="table table-hover table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Plant</th>
                <th>Departemen</th>
                <th>Level 4</th>
                <th>Level 3</th>
                <th>Level 2</th>
                <th>Level 1</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal Hirarki Approval -->
<div class="modal fade" id="hirarkiModal" tabindex="-1" aria-labelledby="hirarkiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-gradient bg-primary text-white border-0">
        <h6 class="modal-title fw-semibold" id="hirarkiModalLabel">
         Tambah / Edit Hirarki Approval
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Hidden input -->
      <input type="hidden" id="hirarki_id" name="hirarki_id">

      <!-- Body -->
      <div class="modal-body bg-light">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Plant</label>
                <select class="form-select" id="plant_id" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
                    <option value="" selected disabled>Plant</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Departemen</label>
                <select class="form-select" id="departemen_id" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
                    <option value="" selected disabled>Departemen</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Level 4</label>
                <input type="text" id="level4" class="form-control" placeholder="Pilih Level 4"
                    style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
                <input type="hidden" id="level4_username">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Level 3</label>
                <input type="text" id="level3" class="form-control" placeholder="Pilih Level 3"
                    style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
                <input type="hidden" id="level3_username">
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
        <label class="form-label fw-semibold">Level 2</label>
        <input type="text" id="level2" class="form-control" placeholder="Pilih Level 2"
            style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
        <input type="hidden" id="level2_username">
    </div>

    <!-- LEVEL 1 (MULTIPLE) -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Level 1</label>
        <input type="text" id="level1" class="form-control" placeholder="Ketik nama lalu pilih..."
            style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 0.75rem center;background-size:8px 10px;">
        <div id="selectedLevel1Wrapper" style="max-height: 90px; overflow-y: auto; border: 1px solid #ddd; padding:6px; border-radius:6px;">
            <div id="selectedLevel1"></div>
        </div>
        <input type="hidden" id="level1_username" name="level1_username">
    </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Batal
        </button>
        <button type="button" class="btn btn-primary px-4" onclick="btnSimpanHirarki()">
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
    loadPlant();
    loadDept();
    resetLevels();
    $('#tabel').DataTable({
        serverSide: false,
        ajax: {
            url: '/userHirarkis/data',
            type: 'POST',
            dataSrc: 'data',
            beforeSend: function () {
                Swal.fire({
                    title: 'Load',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            complete: function () {
                Swal.close();
            }
        },
        scrollY: '400px',
        scrollX: true,
        scrollCollapse: true,
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
            { data: 'nama_plant', className: 'text-start' },
            { data: 'nama_departemen', className: 'text-start' },
            { data: 'level4_name', className: 'text-start',
                render: function(data, type, row) {
                    return data ? `<span class="badge bg-primary text-white fw-normal px-2 py-1" style="font-size: 0.75rem;">${row.level4_name}</span>` : '-';
                }
            },
            { data: 'level3_name', className: 'text-start',
                render: function(data, type, row) {
                    return data ? `<span class="badge bg-primary text-white fw-normal px-2 py-1" style="font-size: 0.75rem;">${row.level3_name}</span>` : '-';
                }
            },
            { data: 'level2_name', className: 'text-start',
                render: function(data, type, row) {
                    return data ? `<span class="badge bg-primary text-white fw-normal px-2 py-1" style="font-size: 0.75rem;">${row.level2_name}</span>` : '-';
                }
            },
            { data: 'level1_name', className: 'text-start',
                render: function (data, type, row) {
                    if (!row.level1_name) return '-';
                    // Jika data berupa string JSON → ubah ke array
                    let arr = row.level1_name;
                    if (typeof row.level1_name === 'string') {
                        try {arr = JSON.parse(row.level1_name);
                        } catch (e) {
                            return row.level1_name;
                        }
                    }
                    // Pastikan array
                    if (!Array.isArray(arr)) return row.level1_name;
                    // Buat badge username
                    return arr.map(u => `<span class="badge bg-primary text-white fw-normal px-2 py-1" style="font-size: 0.70rem; me-1">${u}</span>`).join('');
                }
            },
            { data: null,
                render: () => `
                    <button class="btn btn-sm btn-warning me-1 btn-edit">Edit</button>
                    <button class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                `,
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: "120px"
            }
        ]
    });

    $('#tabel').on('click', '.btn-edit', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data();
        resetLevels()
        btnEditHirarki(data);
    });

    $('#tabel').on('click', '.btn-hapus', function() {
        var table = $('#tabel').DataTable();
        var data = table.row($(this).closest('tr')).data();
        btnHapusHirarki(data.id_hirarki);
    });

    $('#hirarkiModal').on('shown.bs.modal', function() {
        resetLevels();
        loadLevel();
    });

    // Pilihan Plant / Departemen berubah → reload level
    $('#plant_id, #departemen_id').on('change', function() {
        resetLevels();
        loadLevel();
    }); 
});

// ===============================
// BUTTON TAMBAH / EDIT
// ===============================
function btnTambahHirarki() {
    mode = 'tambah';
    $('#hirarki_id').val('');
    $('#plant_id').val('');
    $('#departemen_id').val('');
    $('#level4_username').val('');
    $('#level3_username').val('');
    $('#level2_username').val('');
    $('#level1_username').val('');
    $("#selectedLevel1").html("");
    $('#hirarkiModal').modal('show');
}

function btnEditHirarki(data) {
    mode = 'edit';
    $('#hirarkiModal').modal('show');

    $('#hirarki_id').val(data.id_hirarki);
    $('#plant_id').val(data.plant_id);
    $('#departemen_id').val(data.departemen_id);
    loadLevel(() => {

        $('#level4').val(data.level4_name ?? "");
        $('#level4_username').val(data.level4_us ?? "");

        $('#level3').val(data.level3_name ?? "");
        $('#level3_username').val(data.level3_us ?? "");

        $('#level2').val(data.level2_name ?? "");
        $('#level2_username').val(data.level2_us ?? "");

        // --- Level 1 multiple chips ---
        if (Array.isArray(data.level1_name)) {
            $("#selectedLevel1").html(""); // reset

            data.level1_name.forEach((level1_name, i) => {
                $("#selectedLevel1").append(`
                    <span class="chip-level1" style="background:#eaf6e6;border:1px solid #cfe7cf;border-radius:14px;padding:3px 7px;font-size:10px;">
                        ${level1_name}
                        <span style="margin-left:8px;cursor:pointer;color:#d9534f;" onclick="removeLevel1('${data.level1_us[i]}', this)">×</span>
                    </span>
                `);
            });
            $('#level1_username').val(data.level1_us.join(","));
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

function btnSimpanHirarki() {
    let id_hirarki = $('#hirarki_id').val();
    let plant_id = $('#plant_id').val();
    let departemen_id = $('#departemen_id').val();
    let level4 = $('#level4_username').val();
    let level3 = $('#level3_username').val();
    let level2 = $('#level2_username').val();
    let level1 = $('#level1_username').val();

    if (!plant_id) {
        setErrorFocus('#plant_id');
        return Swal.fire('Peringatan', 'Plant wajib diisi', 'warning');
    }

    if (!departemen_id) {
        setErrorFocus('#departemen_id');
        return Swal.fire('Peringatan', 'Departemen wajib diisi', 'warning');
    }

    if (!level1) {
        setErrorFocus('#level1_username');
        return Swal.fire('Peringatan', 'Level 1 wajib diisi', 'warning');
    }



    let data = {
        id_hirarki,
        plant_id,
        departemen_id,
        level4_us: level4,
        level3_us: level3,
        level2_us: level2,
        level1_us: level1.split(',')
    };
    console.log("Kirim Data:", data);
    let url = '';
    if (mode === 'tambah') {
        url = '/userHirarkis/create';
    } else {
        url = '/userHirarkis/update/' + id_hirarki;
    }
    console.log("URL:", url);
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response) {
            Swal.fire('Berhasil', response.message, 'success');
            $('#hirarkiModal').modal('hide');
            $('#tabel').DataTable().ajax.reload(null, false);
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan', 'error');
        }
    });
}

// ===============================
// BUTTON HAPUS
// ===============================
function btnHapusHirarki(id_hirarki) {
    Swal.fire({
        title: 'Yakin ingin menghapus data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url: `/userHirarkis/delete/${id_hirarki}`,
                type: 'POST',
                success: function(response){
                    $('#tabel').DataTable().ajax.reload(null, false);
                    Swal.fire('Berhasil', response.message, 'success');
                },
                error: function(xhr, status, error) {
                    let message = "Terjadi kesalahan saat menghapus.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal',message, 'error');
                }
            });
        }
    });
}


function loadPlant() {
    $.ajax({
        url: '/userHirarkis/loadPlant',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            let dropdown = $('#plant_id');
            dropdown.empty();
            dropdown.append('<option value="">-- Pilih Plant --</option>');
            $.each(res.data, function(i, plant) {
                dropdown.append(`<option value="${plant.id_plant}">${plant.nama_plant}</option>`);
            });
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data plant', 'error');
        }
      });
    }


  function loadDept() {
    $.ajax({
        url: '/userHirarkis/loadDept',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            let dropdown = $('#departemen_id');
            dropdown.empty();
            dropdown.append('<option value="">-- Pilih Departemen --</option>');
            $.each(res.data, function(i, dept) {
                dropdown.append(`<option value="${dept.id_departemen}">${dept.nama_departemen}</option>`);
            });
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data departemen', 'error');
        }
      });
    }

    // ===============================
// RESET LEVELS
// ===============================
function resetLevels() {
    $('#level1, #level2, #level3, #level4').val('');
    $('#level1_username, #level2_username, #level3_username, #level4_username').val('');
    $('#level1, #level2, #level3, #level4').css('border', '');
}

function loadLevel(callback = null) {
    let plant = $('#plant_id').val();
    let dept = $('#departemen_id').val();

    if (!plant || !dept) return;

    Swal.fire({
        title: 'Load',
        text: 'Mohon tunggu sebentar.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '/userHirarkis/loadLevel',
        type: 'POST',
        dataType: 'json',
        data: { plant_id: plant, departemen_id: dept },
        success: function(res) {
            if (!res.success) return;

            let level1Data = res.data.level1.map(u => ({
                label: u.nama_lengkap,
                value: u.nama_lengkap,
                username: u.username,
                position: u.nama_position
            }));
            initLevel1(level1Data); // MULTIPLE

            let level2Data = res.data.level2.map(u => ({
                label: u.nama_lengkap,
                value: u.nama_lengkap,
                username: u.username,
                position: u.nama_position
            }));
            initLevel2(level2Data); // SINGLE

            let level3Data = res.data.level3.map(u => ({
                label: u.nama_lengkap,
                value: u.nama_lengkap,
                username: u.username,
                position: u.nama_position
            }));
            initLevel3(level3Data); // SINGLE

            let level4Data = res.data.level4.map(u => ({
                label: u.nama_lengkap,
                value: u.nama_lengkap,
                username: u.username,
                position: u.nama_position
            }));
            initLevel4(level4Data); // SINGLE

            if (callback) callback();
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            Swal.fire('Error', 'Gagal memuat data level', 'error');
        },
        complete: function() {
             Swal.close();
        }
    });
}

/* ==========================
    LEVEL 1 - MULTIPLE
   (chip + hidden username comma-separated)
========================== */
function initLevel1(sourceData) {
    $('#level1').autocomplete({
        source: sourceData,
        minLength: 0,
        delay: 50,
        select: function(event, ui) {
            let selectedUsernames = $('#level1_username').val() ? $('#level1_username').val().split(',') : [];

            if (!selectedUsernames.includes(ui.item.username)) {
                selectedUsernames.push(ui.item.username);
                $('#level1_username').val(selectedUsernames.join(','));

                $("#selectedLevel1").append(`
                    <span class="chip-level1" data-username="${ui.item.username}"
                          style="display:inline-flex;align-items:center;background:#eaf6e6;border:1px solid #cfe7cf;border-radius:14px;padding:3px 7px;font-size:10px;">
                        ${ui.item.label}
                        <span onclick="removeLevel1('${ui.item.username}', this)" style="margin-left:8px;cursor:pointer;font-weight:bold;color:#d9534f;">×</span>
                    </span>
                `);
            } else {
                Swal.fire('Warning', `${ui.item.label} sudah dipilih sebelumnya!`, 'warning');
            }

            $('#level1').val("");
            return false;
        },
    }).on('focus click', function(){
        $(this).autocomplete("search", "");
    });
}

function removeLevel1(username, el) {

    Swal.fire({
        title: "Hapus?",
        text: "Apakah Anda yakin ingin menghapus user ini dari Level 1?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            // Update username list
            let usernames = $('#level1_username').val().split(',').filter(u => u !== username);
            $('#level1_username').val(usernames.join(','));

            // Hapus chip UI
            $(el).parent().fadeOut(200, function () {
                $(this).remove();

                // Sinkron ulang input nama berdasarkan chip yang tersisa
                let remainingNames = [];
                $('#selectedLevel1 .chip-level1').each(function () {
                    let name = $(this).contents().get(0).nodeValue.trim();
                    remainingNames.push(name);
                });

                $('#level1').val(remainingNames.join(', '));
            });
        }

    });
}



/* ==========================
    LEVEL 2 - SINGLE
========================== */
function initLevel2(sourceData) {
    $('#level2').autocomplete({
        source: sourceData,
        minLength: 0,
        select: function(e, ui) {
            $('#level2_username').val(ui.item.username);
        },
    }).on('focus click', function(){
        $(this).autocomplete("search", "");
    });
}

/* ==========================
    LEVEL 3 - SINGLE
========================== */
function initLevel3(sourceData) {
    $('#level3').autocomplete({
        source: sourceData,
        minLength: 0,
        select: function(e, ui) {
            $('#level3_username').val(ui.item.username);
        },
    }).on('focus click', function(){
        $(this).autocomplete("search", "");
    });
}

/* ==========================
    LEVEL 4 - SINGLE
========================== */
function initLevel4(sourceData) {
    $('#level4').autocomplete({
        source: sourceData,
        minLength: 0,
        select: function(e, ui) {
            $('#level4_username').val(ui.item.username);
        },
    }).on('focus click', function(){
        $(this).autocomplete("search", "");
    });
}


</script>
@endsection
