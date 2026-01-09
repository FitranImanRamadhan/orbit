<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\MstTicketController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\TicketingController;
use App\Http\Controllers\UserHirarkiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;
use App\Models\Plant;

// ==============================
// ROUTE UNTUK USER BELUM LOGIN (guest)
// ==============================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ==============================
// ROUTE UNTUK USER SUDAH LOGIN (auth)
// ==============================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware(['auth', 'akses:all'])->group(function () {
        Route::get('/notifications/getNotif', [NotificationController::class, 'getNotifications']);
        Route::post('/notifications/read', [NotificationController::class, 'markRead']);
        Route::get('/notifications/check-redirect', [NotificationController::class, 'checkRedirect']);
    });

    Route::middleware(['auth', 'akses:developer,superAdmin,admin'])->group(function () {
        // ========================================MASTER=======================================================
        Route::prefix('plants')->group(function () {
            Route::get('/', [PlantController::class, 'index'])->name('plants.index');
            Route::post('/data', [PlantController::class, 'data'])->name('plants.data');
            Route::post('/create', [PlantController::class, 'create'])->name('plants.create');
            Route::post('/update/{id}', [PlantController::class, 'update'])->name('plants.update');
            Route::delete('/delete/{id}', [PlantController::class, 'destroy'])->name('softwares.destroy');
        });
        Route::prefix('departemens')->group(function () {
            Route::get('/', [DepartemenController::class, 'index'])->name('departemens.index');
            Route::post('/data', [DepartemenController::class, 'data'])->name('departemens.data');
            Route::post('/create', [DepartemenController::class, 'create'])->name('departemens.create');
            Route::post('/{id}', [DepartemenController::class, 'update'])->name('departemens.update');
            Route::delete('/{id}', [DepartemenController::class, 'destroy'])->name('departemens.destroy');
        });
        Route::prefix('positions')->group(function () {
            Route::get('/', [PositionController::class, 'index'])->name('positions.index');
            Route::post('/data', [PositionController::class, 'data'])->name('positions.data');
            Route::post('/loadDept', [PositionController::class, 'loadDept'])->name('positions.loadDept');
            Route::post('/create', [PositionController::class, 'create'])->name('positions.create');
            Route::post('/{id}', [PositionController::class, 'update'])->name('positions.update');
            Route::delete('/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');
        });
        Route::prefix('tickets')->group(function () {
            Route::get('/', [MstTicketController::class, 'index'])->name('tickets.index');
            Route::post('/data', [MstTicketController::class, 'data'])->name('tickets.data');
            Route::post('/create', [MstTicketController::class, 'create'])->name('tickets.create');
            Route::post('/update/{id}', [MstTicketController::class, 'update'])->name('tickets.update');
            Route::delete('delete/{id}', [MstTicketController::class, 'destroy'])->name('tickets.destroy');
        });
    });
    Route::middleware(['auth', 'akses:developer,superAdmin'])->group(function () {
        Route::prefix('hardwares')->group(function () {
            Route::get('/', [HardwareController::class, 'index'])->name('hardwares.index');
            Route::post('/data', [HardwareController::class, 'data'])->name('hardwares.data');
            Route::post('/create', [HardwareController::class, 'create'])->name('hardwares.create');
            Route::post('/update/{id}', [HardwareController::class, 'update'])->name('hardwares.update');
            Route::delete('delete/{id}', [HardwareController::class, 'destroy'])->name('hardwares.destroy');
        });
        Route::prefix('softwares')->group(function () {
            Route::get('/', [SoftwareController::class, 'index'])->name('softwares.index');
            Route::post('/data', [SoftwareController::class, 'data'])->name('softwares.data');
            Route::post('/create', [SoftwareController::class, 'create'])->name('softwares.create');
            Route::post('/update/{id}', [SoftwareController::class, 'update'])->name('softwares.update');
            Route::delete('/delete/{id}', [SoftwareController::class, 'destroy'])->name('softwares.destroy');
        });
    });


    Route::middleware(['auth', 'akses:developer,superAdmin,admin'])->group(function () {
        Route::prefix('userHirarkis')->group(function () {
            Route::get('/', [UserHirarkiController::class, 'index'])->name('userHirarkis.index');
            Route::post('/data', [UserHirarkiController::class, 'data'])->name('userHirarkis.data');
            Route::post('/loadPlant', [UserHirarkiController::class, 'loadPlant'])->name('userHirarkis.loadPlant');
            Route::post('/loadDept', [UserHirarkiController::class, 'loadDept'])->name('userHirarkis.loadDept');
            Route::post('/loadLevel', [UserHirarkiController::class, 'loadLevel'])->name('userHirarkis.loadLevel');
            Route::post('/create', [UserHirarkiController::class, 'create'])->name('userHirarkis.create');
            Route::post('/update/{id_hirarki}', [UserHirarkiController::class, 'update'])->name('userHirarkis.update');
            Route::post('/delete/{id_hirarki}', [UserHirarkiController::class, 'destroy'])->name('userHirarkis.delete');
        });
    });

    Route::prefix('users')->group(function () {
        Route::middleware(['auth', 'akses:developer,superAdmin,admin'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/data', [UserController::class, 'data'])->name('users.data');
            Route::post('/data_dblink', [UserController::class, 'data_dblink'])->name('users.data_dblink');
            Route::post('/loadPlant', [UserController::class, 'loadPlant'])->name('users.loadPlant');
            Route::post('/loadDept', [UserController::class, 'loadDept'])->name('users.loadDept');
            Route::post('/loadSect', [UserController::class, 'loadSect'])->name('users.loadSect');
            Route::post('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/update/{id_user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('delete/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::get('/profile', [UserController::class, 'profile'])->name('profile'); // tampilkan halaman profile
        Route::post('/profile/data', [UserController::class, 'profiledata'])->name('profile.data');
        Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update'); // update profile

    });

    // =======================================TRANSAKSI=======================================================
    Route::prefix('ticketings')->group(function () {
        Route::post('/getHardware', [TicketingController::class, 'getHardware'])->name('ticketing.getHardware');
        Route::post('/getSoftware', [TicketingController::class, 'getSoftware'])->name('ticketing.getSoftware');
        Route::post('/getDept', [TicketingController::class, 'getDept'])->name('ticketings.getDept');
        Route::post('/getDeptHead', [TicketingController::class, 'getDeptHead'])->name('ticketings.getDeptHead');

        //create
        // Route::middleware(['auth', 'akses:developer,admin,userNonIT'])->group(function () {
        Route::get('/create_ticket', [TicketingController::class, 'create_ticket'])->name('ticketing.create_ticket');
        Route::post('/create_ticket_proses', [TicketingController::class, 'create_ticket_proses'])->name('ticketing.create_ticket_proses');
        // });

        //approval
        Route::middleware(['auth', 'akses:developer,admin,superAdmin,userNonIT'])->group(function () {
            Route::get('/approval', [TicketingController::class, 'approval'])->name('ticketing.approval');
            Route::post('/data_approval', [TicketingController::class, 'data_approval'])->name('ticketing.data_approval');
            Route::post('/approval_proses', [TicketingController::class, 'approval_proses'])->name('ticketing.approval_proses');
        });

        //track
        Route::middleware(['auth', 'akses:developer,admin,userNonIT'])->group(function () {
            Route::get('/track_ticket', [TicketingController::class, 'track_ticket'])->name('ticketing.track_ticket');
            Route::post('/data_track', [TicketingController::class, 'data_track'])->name('ticketing.data_track');
        });

        //queue
        Route::middleware(['auth', 'akses:all'])->group(function () {
            Route::get('/queue_ticket', [TicketingController::class, 'queue_ticket'])->name('ticketing.queue_ticket');
            Route::post('/data_queue', [TicketingController::class, 'data_queue'])->name('ticketing.data_queue');
        });

        //incoming hw
        Route::middleware(['auth', 'akses:developer,isAdminIt,isTS'])->group(function () {
            Route::get('/incoming_hardware', [TicketingController::class, 'incoming_hardware'])->name('ticketing.incoming_hardware');
            Route::post('/data_incoming_hardware', [TicketingController::class, 'data_incoming_hardware'])->name('ticketing.data_incoming_hardware');
            Route::post('/hw_start_proses', [TicketingController::class, 'hw_start_proses'])->name('ticketing.hw_start_proses');
            Route::post('/hw_finish_proses', [TicketingController::class, 'hw_finish_proses'])->name('ticketing.hw_finish_proses');
        });

        //incoming sw
        Route::middleware(['auth', 'akses:developer,isAdminIt,isImplementator'])->group(function () {
            Route::get('/incoming_software', [TicketingController::class, 'incoming_software'])->name('ticketing.incoming_software');
            Route::post('/data_incoming_software', [TicketingController::class, 'data_incoming_software'])->name('ticketing.data_incoming_software');
            Route::post('/sw_start_proses', [TicketingController::class, 'sw_start_proses'])->name('ticketing.sw_start_proses');
            Route::post('/sw_finish_proses', [TicketingController::class, 'sw_finish_proses'])->name('ticketing.sw_finish_proses');
            Route::get('/incoming-software/pdf/preview', [TicketingController::class, 'incoming_software_pdf']);
        });

        //report sw
        Route::middleware(['auth', 'akses:developer,isLeaderImp,isAdminIt,isImplementator,isAsmenIt'])->group(function () {
            Route::get('/report/report_ticket_software', [ReportController::class, 'report_ticket_software'])->name('ticketing.report_ticket_software');
            Route::post('/data_report_software', [TicketingController::class, 'data_report_software'])->name('ticketing.data_report_software');
            Route::post('/report/data_report_ticket_software', [ReportController::class, 'data_report_ticket_software'])->name('ticketing.data_report_ticket_software');
            Route::get('/report/chart_ticket_software', function () {
                return view('ticketings.report.chart_ticket_software');
            });
            Route::post('/report/data_chart_ticket_software', [ReportController::class, 'data_chart_ticket_software'])->name('ticketing.data_chart_ticket_software');
        });

        Route::post('/report/create_report_ticket', [ReportController::class, 'create_report_ticket'])->name('ticketing.create_report_ticket');
        //report hw
        Route::middleware(['auth', 'akses:developer,isLeaderImp,isAdminIt,isTS,isAsmenIt'])->group(function () {
            Route::get('/report/report_ticket_hardware', [ReportController::class, 'report_ticket_hardware'])->name('ticketing.report_ticket_hardware');
            Route::post('/report/data_report_ticket_hardware', [ReportController::class, 'data_report_ticket_hardware'])->name('ticketing.data_report_ticket_hardware');
            Route::get('/report/chart_ticket_hardware', function () {return view('ticketings.report.chart_ticket_hardware');});
            Route::post('/report/data_chart_ticket_hardware', [ReportController::class, 'data_chart_ticket_hardware'])->name('ticketing.data_chart_ticket_hardware');
        });

        Route::middleware(['auth', 'akses:developer,superAdmin'])->group(function () {
            Route::get('/report/report_approval', [ReportController::class, 'report_approval'])->name('ticketing.report_approval');
            Route::get('/report/export_excel', [ReportController::class, 'export_excel']);
            Route::post('/report/data_report_approval', [ReportController::class, 'data_report_approval'])->name('ticketing.data_report_approval');
        });
        Route::middleware(['auth', 'akses:developer,isLeaderTs,isLeaderImp,isAsmenIt'])->group(function () {
            Route::post('/report/proses_approval_report_ticket', [ReportController::class, 'proses_approval_report_ticket'])->name('ticketing.proses_approval_report_ticket');
        });

        Route::get('/user_confirm_hardware', [TicketingController::class, 'user_confirm_hardware'])->name('ticketing.user_confirm_hardware');
        Route::post('/data_user_confirm_hardware', [TicketingController::class, 'data_user_confirm_hardware'])->name('ticketing.data_user_confirm_hardware');
        Route::post('/proses_user_confirm_hardware', [TicketingController::class, 'proses_user_confirm_hardware'])->name('ticketing.proses_user_confirm_hardware');
    });

    Route::post('/chat/send', [ChatController::class, 'send']);
    Route::post('/chat/kirim', [ChatController::class, 'kirim']);
    Route::post('/chat/getChats', [ChatController::class, 'getChats']);
    Route::post('/chat/read', [ChatController::class, 'read']);
});
