@extends('layout')

@section('content')
<div class="text-center">
    <h3 id="title1" class="fw-bold mb-3">Pilih Jenis Ticket</h3>
    <div class="d-flex justify-content-center gap-2">
        <button id="btnSoftware" type="button" value="software" onclick="btnSoftware()" class="btn btn-primary px-4 py-2"><i class="fa fa-laptop-code me-1"></i> Software</button>
        <button id="btnHardware" type="button" value="hardware" onclick="btnHardware()" class="btn btn-dark px-4 py-2"><i class="fa fa-desktop me-1"></i> Hardware</button>
        <button type="button" onclick="reset()" class="btn btn-outline-secondary"><i class="fa fa-sync"></i> Reset </button>
    </div>
</div>

<div class="my-4 d-flex justify-content-center">
    <div id="form-card" class="card shadow-sm p-4" style="max-width: 900px; width: 100%; border-radius: 12px;">
        <form action="javascript:void(0)">
            <h3 id="title2" class="fw-bold mb-4 text-center" style="letter-spacing:.5px;"></h3>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Permintaan</label>
                        <input type="date" id="tgl_permintaan" class="form-control">
                    </div>
                    <div id="dropdownHardware" class="mb-3">
                        <label class="form-label fw-semibold">Daftar Komputer</label>
                        <input type="text" id="daftar_komputer" class="form-control"  placeholder="Ketik nama komputer..." style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                        <input type="hidden" id="id_hardware">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="deskripsi" class="form-control" rows="5" placeholder="Tulis deskripsi masalah..."></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="dropdownKlaim" class="mb-3">
                        <label class="form-label fw-semibold">Kategori Klaim</label>
                        <select id="kategori_klaim" class="form-select" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="ui">UI</option>
                            <option value="function">Function</option>
                            <option value="output">Output</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="text" id="kategori_manual" class="form-control mt-2" placeholder="Masukkan kategori lain..." style="display:none;">
                    </div>
                    <div id="dropdownSoftware" class="mb-3">
                        <label class="form-label fw-semibold">Daftar Software</label>
                        <input type="text" id="daftar_software" class="form-control" placeholder="Ketik nama software..." style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 4 5%27%3E%3Cpath fill=%27%23333%27 d=%27M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z%27/%3E%3C/svg%3E'); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:8px 10px;">
                        <input type="hidden" id="id_software">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold d-block">Upload Bukti / Lampiran (Opsional)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="javascript:void(0)" id="btnPlus" onclick="btnPlus()" class="btn btn-success btn-sm d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="fa fa-plus"></i>
                            </a>
                            <div id="fileContainer" class="w-100">
                                <input id="file1" type="file" class="form-control mb-2">
                                <input id="file2" type="file" class="form-control mb-2" disabled>
                                <input id="file3" type="file" class="form-control mb-2" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="button" onclick="btnSubmit()" class="btn btn-primary px-4 py-2" style="border-radius: 8px;"><i class="fa fa-paper-plane me-1"></i> Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let jenisTicket = null;
$(document).ready(function(){
    validateFile('file1');
    validateFile('file2');
    validateFile('file3');
    reset();

    let hardwareList = [];
    $.ajax({
        url: '/ticketings/getHardware',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                hardwareList = res.data.map(item => ({
                    label: item.nama_hardware,
                    value: item.nama_hardware,
                    id: item.id_hardware
                }));

                $('#daftar_komputer').autocomplete({
                    minLength: 0,
                    source: hardwareList,
                    select: function(event, ui) {
                        $(this).val(ui.item.label);
                        $('#id_hardware').val(ui.item.id);
                        return false;
                    }
                });

                $('#daftar_komputer').on('input change blur', function() {
                    let val = $(this).val().trim();
                    if (!val) {
                        $('#id_hardware').val(''); // reset jika input kosong
                    }
                });

                $('#daftar_komputer').on('click', function(){
                    $(this).autocomplete("search", "");
                });
            }
        }
    });

    let softwareList = [];
    $.ajax({
        url: '/ticketings/getSoftware',
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                softwareList = res.data.map(item => ({
                    label: item.nama_software,
                    value: item.nama_software,
                    id: item.id_software
                }));

                $('#daftar_software').autocomplete({
                    minLength: 0,
                    source: softwareList,
                    select: function(event, ui) {
                        $(this).val(ui.item.label);
                        $('#id_software').val(ui.item.id);
                        return false;
                    }
                });
                $('#daftar_software').on('input change blur', function() {
                    let val = $(this).val().trim();
                    if (!val) {
                        $('#id_software').val(''); // reset jika input kosong
                    }
                });
                $('#daftar_software').on('click', function(){
                    $(this).autocomplete("search", "");
                });
            }
        }
    });
});

$(function() {
    jenisTicket = null;
    $('#form-card').hide();
    $('#title1').text('Pilih Jenis Ticket');
    $('#btnSoftware').prop('disabled', false).removeClass('opacity-50');
    $('#btnHardware').prop('disabled', false).removeClass('opacity-50');
    $('#tgl_permintaan').val('');
    $('#daftar_komputer').val('');
    $('#dept_head').val('');
    $('#deskripsi').val('');
    $('#kategori_klaim').val('');
    $('#kategori_manual').val('').hide(); // sembunyikan input manual
    $('#daftar_software').val('');
    $('#file1').val('');
    $('#file2').val('').prop('disabled', true);
    $('#file3').val('').prop('disabled', true);
    $('#btnPlus').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
    $('#dropdownHardware').show();
    $('#dropdownDeptHead').show();
    $('#dropdownSoftware').show();
    $('#dropdownKlaim').show();

    // Input manual kategori Other
    $('#kategori_klaim').on('change', function(){
        if($(this).val() === 'other'){
            $('#kategori_manual').show();
        } else {
            $('#kategori_manual').hide().val('');
        }
    });

});

function btnSoftware() {
    jenisTicket = 'software';
    console.log(jenisTicket);
    $('#title2').text('Form Ticket Software');
    $('#title1').text('Ticket Software');
    $('#form-card').slideDown();
    $('#btnHardware').prop('disabled', true).addClass('opacity-50');
    $('#dropdownHardware').hide();
    $('#daftar_komputer').val('');
    $('#id_hardware').val('');
    $('#dept_us').val('');
    $('#dropdownDeptHead').hide();
}

function btnHardware() {
    jenisTicket = 'hardware';
    console.log(jenisTicket);
    $('#title2').text('Form Ticket Hardware');
    $('#title1').text('Ticket Hardware');
    $('#form-card').slideDown();
    $('#btnSoftware').prop('disabled', true).addClass('opacity-50');
    $('#dropdownSoftware').hide();
    $('#daftar_software').val('');
    $('#id_software').val('');
    $('#kategori_klaim').val('');
    $('#kategori_manual').val('');
    $('#dropdownKlaim').hide();
}

// Tombol tambah file
function btnPlus() {
    if ($('#file2').prop('disabled')) {
        $('#file2').prop('disabled', false);
    } else if ($('#file3').prop('disabled')) {
        $('#file3').prop('disabled', false);
    }

    if (!$('#file2').prop('disabled') && !$('#file3').prop('disabled')) {
        $('#btnPlus').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
    }
}

// Tombol back
function reset() {
    jenisTicket = null;
    $('#form-card').hide();
    $('#title1').text('Pilih Jenis Ticket');
    $('#btnSoftware').prop('disabled', false).removeClass('opacity-50');
    $('#btnHardware').prop('disabled', false).removeClass('opacity-50');
    $('#tgl_permintaan').val('');
    $('#daftar_komputer').val('');
    $('#id_hardware').val('');
    $('#daftar_software').val('');
    $('#id_software').val('');
    $('#deskripsi').val('');
    $('#kategori_klaim').val('');
    $('#kategori_manual').val('').hide();
    $('#file1').val('');
    $('#file2').val('').prop('disabled', true);
    $('#file3').val('').prop('disabled', true);
    $('#btnPlus').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
    $('#dropdownHardware').show();
    $('#dropdownDeptHead').show();
    $('#dropdownSoftware').show();
    $('#dropdownKlaim').show();
}

const imageTypes = ['image/jpeg','image/png','image/gif'];
const docTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
function validateFile(inputId) {
    $('#' + inputId).on('change', function() {
        let file = this.files[0];
        if (!file) return;

        let type = file.type;
        let sizeMB = file.size / 1024 / 1024;
        let ext = file.name.split('.').pop().toLowerCase();

        if (imageTypes.includes(type) && sizeMB > 5) {
            Swal.fire('Warning', `File .${ext} terlalu besar (max 5MB untuk gambar)`, 'warning');
            this.value = '';
        } else if (docTypes.includes(type) && sizeMB > 15) {
            Swal.fire('Warning', `File .${ext} terlalu besar (max 15MB untuk dokumen)`, 'warning');
            this.value = '';
        } else if (!imageTypes.includes(type) && !docTypes.includes(type)) {
            Swal.fire('Warning', `File .${ext} tipe tidak diperbolehkan`, 'warning');
            this.value = '';
        }
    });
}

// Tombol submit
function setErrorFocus(selector) {
    $(selector)
        .css('border', '1px solid red')
        .focus()
        .one('input change keyup click', function () {
            $(this).css('border', '');
        });
}
function btnSubmit() {
    let tglPermintaan = $('#tgl_permintaan').val();
    let dept_us = $('#dept_us').val();
    let id_hardware = $('#id_hardware').val();
    let id_software = $('#id_software').val();
    let deskripsi = $('#deskripsi').val();
    let kategoriKlaim = $('#kategori_klaim').val() === 'other' ? $('#kategori_manual').val() : $('#kategori_klaim').val();
    console.log('id_software:', id_software, 'kategoriKlaim:', kategoriKlaim);
    // ==== Validasi input ====
   
    if (!jenisTicket) {setErrorFocus('#jenis_ticket'); Swal.fire('Perhatian', 'Silakan pilih jenis ticket terlebih dahulu.', 'warning'); return;}
    if (!tglPermintaan) {setErrorFocus('#tgl_permintaan'); Swal.fire('Perhatian', 'Tanggal permintaan wajib diisi.', 'warning'); return;}
    if (jenisTicket === 'software') {
        if (!id_software) {
            setErrorFocus('#daftar_software');
            Swal.fire('Perhatian', 'Silakan pilih software yang bermasalah.', 'warning');
            return;
        }
        if (!kategoriKlaim) {
            setErrorFocus('#kategori_klaim');
            Swal.fire('Perhatian', 'Kategori klaim wajib diisi.', 'warning');
            return;
        }
    }
    if (jenisTicket === 'hardware') {
        if (!id_hardware) {setErrorFocus('#daftar_komputer'); Swal.fire('Perhatian', 'Silakan pilih hardware yang bermasalah.', 'warning'); return;}
    }
    if (!deskripsi) {setErrorFocus('#deskripsi'); Swal.fire('Perhatian', 'Deskripsi Wajib diisi.', 'warning'); return;}
    

    // ==== Tentukan item_ticket berdasarkan jenis_ticket ====
    let itemTicket = (jenisTicket === 'software') ? id_software : id_hardware;
    // ==== Buat FormData ====
    let formData = new FormData();
    formData.append('jenisTicket', jenisTicket);
    formData.append('tglPermintaan', tglPermintaan);
    formData.append('dept_us', dept_us);
    formData.append('item_ticket', itemTicket);   // ← PENTING
    formData.append('deskripsi', deskripsi);
    formData.append('kategoriKlaim', kategoriKlaim);
    // FILE (opsional)
    if ($('#file1')[0].files.length > 0) formData.append('file1', $('#file1')[0].files[0]);
    if ($('#file2')[0].files.length > 0) formData.append('file2', $('#file2')[0].files[0]);
    if ($('#file3')[0].files.length > 0) formData.append('file3', $('#file3')[0].files[0]);
    
    $.ajax({
        url: '/ticketings/create_ticket_proses',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function() {
            Swal.fire({
                title: 'Loading...',
                text: 'Sedang mengirim data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },
        success: function(res) {
            Swal.close();

            if (res.success === true) {
                Swal.fire('Berhasil', res.message || 'Ticket berhasil dibuat', 'success');
            } else {
                Swal.fire('Info', res.message || 'Ticket gagal dibuat', 'info');
            }

            reset();
        },
        error: function(xhr) {
            Swal.close();
            console.error(xhr.responseText);
            let message = 'Terjadi kesalahan server';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire('Error', message, 'error');
        }
    });
}


</script>
@endsection
