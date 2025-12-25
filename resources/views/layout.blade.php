<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- ✅ tambahkan ini -->
    <link rel="icon" href="public/assets/img/logo/1.png" type="image/x-icon" />
    <link rel="icon" href="{{ asset('assets/img/logo/1.png') }}" type="image/x-icon">
    <title>Banshu Ticketing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/jquery-ui/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
 

    <!-- <style>
     .sticky-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            background-color: #e4e9f7;
       margin-left: 10px;
        }
    </style> -->

    <style>
        th {
            padding-top: 4px;
            padding-bottom: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .ui-autocomplete {
            z-index: 2000 !important;
        }

        /* Hover icon effect */
        .notif-icon:hover,
        .profile-icon:hover {
            color: #0d6efd !important;
            cursor: pointer;
            transition: color 0.2s;
        }

        /* Badge pulse effect */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
        }

        .badge-pulse {
            animation: pulse 1s infinite;
        }

        /* Scrollable dropdown */
        .dropdown-menu-scroll {
            max-height: 300px;
            overflow-y: auto;
        }

        .notif-unread {
            background: rgba(255, 0, 0, 0.12);
            /* merah transparan */
            border-left: 4px solid red;
            font-weight: bold;
        }

        .notif-unread:hover {
            background: rgba(255, 0, 0, 0.25);
        }
    </style>
</head>

<body>
    @include('components.navbar')
    <section class="home py-2">
        <div class="sticky-header d-flex justify-content-between align-items-center">
            <!-- Kiri: Welcome Text -->
            <div class="d-flex align-items-center">
                &nbsp;&nbsp;&nbsp;<p class="mb-0 ms-3 mt-2">Selamat datang,
                    {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}!</p>
            </div>

            <!-- Kanan: Notifikasi & Profile Dropdown -->
            <div class="d-flex align-items-center me-3">
                <!-- Notifikasi -->
                <div class="dropdown me-3">
                    <a class="position-relative text-dark fs-5" href="#" id="notifDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="far fa-bell" style="color:#6c757d;"></i>

                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                            <span class="visually-hidden">unread notifications</span>
                        </span>

                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2"
                        aria-labelledby="notifDropdown" style="min-width: 250px;">
                        <li class="dropdown-header fw-bold">Notifikasi</li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item small d-flex align-items-start" href="">
                                <i class="fa fa-info-circle me-2 text-primary"></i>
                                <div>
                                    <div>

                                    </div>
                                    <small class="text-muted">

                                    </small>
                                </div>
                            </a>
                        </li>

                        <li class="dropdown-item text-center text-muted">Tidak ada notifikasi</li>

                    </ul>
                </div>

                <!-- Profile -->
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#"
                        id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset(Auth::user()->avatar ? 'assets/img/avatar/' . Auth::user()->avatar : 'assets/img/avatar/default-avatar.png') }}"
                            id="avatar" alt="Avatar" width="35" height="35"
                            class="rounded-circle shadow-sm me-2">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2"
                        aria-labelledby="profileDropdown" style="min-width: 200px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                <i class="fa fa-user me-2 text-secondary"></i> Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a id="btnLogout" class="dropdown-item d-flex align-items-center"
                                href="{{ route('logout') }}">
                                <i class="fa fa-sign-out-alt me-2"></i></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <hr style="margin-top: 15px;">

        <div class="container-fluid py-1">
            <div class="card p-4" style="background-color: #e5e8e8; margin: 10px">
                <div class="card-body px-2 py-0"></div>
                @yield('content')
            </div>
        </div>
    </section>



    <script>
        const body = document.querySelector('body'),
            sidebar = body.querySelector('nav'),
            toggle = body.querySelector(".toggle"),
            searchBtn = body.querySelector(".search-box"),
            modeSwitch = body.querySelector(".toggle-switch"),
            modeText = body.querySelector(".mode-text");

        // === Mode Gelap/Terang dari localStorage ===
        if (localStorage.getItem("mode") === "dark") {
            body.classList.add("dark");
            modeText.innerText = "Light mode";
        } else {
            body.classList.remove("dark");
            modeText.innerText = "Dark mode";
        }

        // === Status Awal Sidebar dari localStorage ===
        if (localStorage.getItem("sidebar") === "close") {
            sidebar.classList.add("close");
        } else {
            sidebar.classList.remove("close");
        }

        // === Toggle Sidebar ===
        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            // Simpan status sidebar
            if (sidebar.classList.contains("close")) {
                localStorage.setItem("sidebar", "close");
            } else {
                localStorage.setItem("sidebar", "open");
            }
            // Penyesuaian DataTables
            setTimeout(() => {
                if ($.fn.DataTable.isDataTable('#tabel')) {
                    $('#tabel').DataTable().columns.adjust();
                }
            }, 300);
        });

        // === Toggle Mode Gelap/Terang ===
        modeSwitch.addEventListener("click", () => {
            body.classList.toggle("dark");
            if (body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
                modeText.innerText = "Light mode";
            } else {
                localStorage.setItem("mode", "light");
                modeText.innerText = "Dark mode";
            }
        });
    </script>
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-ui/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chartjs/dist/chart.umd.js') }}"></script>
    <!-- Script -->
  
    
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}

   

    <!-- js untuk input icon boostrap -->
   
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        $(document).on('click', '#btnLogout', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('logout') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            window.location.href = "{{ route('login') }}";
                        },
                        error: function() {
                            Swal.fire('Error', 'Gagal logout. Silakan coba lagi.', 'error');
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            function loadNotifications() {
                $.ajax({
                    url: "/notifications/getNotif",
                    method: "GET",
                    success: function(response) {
                        let badge = $('#notifDropdown .badge');
                        let dropdown = $(".dropdown-menu[aria-labelledby='notifDropdown']");

                        if (response.count > 0) badge.text(response.count).show();
                        else badge.hide();

                        dropdown.find("li:not(.dropdown-header):not(.dropdown-divider)").remove();

                        response.notifications.forEach(notif => {
                            let unreadClass = notif.status !== 'read' ? 'notif-unread' : '';

                            dropdown.append(`
                        <li>
                            <a class="dropdown-item small d-flex align-items-start notif-item ${unreadClass}"
                                href="#"
                                data-id="${notif.id}"
                                data-ticket="${notif.ticket_no}">
                                <i class="fa fa-info-circle me-2"></i>
                                <div>
                                    <div>${notif.message}</div>
                                    <small class="text-muted">${notif.created_at}</small>
                                </div>
                            </a>
                        </li>
                    `);
                        });
                    }
                });
            }

            loadNotifications();
            setInterval(loadNotifications, 4000);

            $(document).on('click', '.notif-item', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let ticketNo = $(this).data('ticket');

                // STEP 1: Mark notification as read
                $.ajax({
                    url: "/notifications/read",
                    type: "POST",
                    data: { id: id },
                    success: function() {
                        // STEP 2: Check redirect
                        $.ajax({
                            url: "/notifications/check-redirect",
                            type: "GET",
                            data: { ticket: ticketNo },
                            success: function(res) {
                                if(res.redirect){
                                    // Redirect ke halaman approval
                                    window.location.href = res.url + '?ticket=' + encodeURIComponent(ticketNo);
                                } else {
                                    loadNotifications(); // hanya update badge notif
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>




    @yield('scripts')
</body>

</html>
