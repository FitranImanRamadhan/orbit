@php
    $user = Auth::user();
    // IT = departemen_id 3
    $isIT = ($user->departemen_id == 3);
    $isNonIT = !$isIT;

    // dd(Auth::user());
    $isSuperAdmin       = ($isIT && $user->user_akses === 'super_admin');
    $isImplementator    = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [37, 1006]));
    $isTS               = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [32, 1007]));
    $isLeaderImp        = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1006]));
    $isLeaderTs         = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1007]));
    $isAdminIt          = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [34]));
    $isAsmenIt          = ($isIT && $user->user_akses === 'super_admin' && in_array($user->position_id, [1001]));
    $isDev              = ($isIT && $user->user_akses === 'developer');

    //non it
    $isUserNonIT        = ($isNonIT && $user->user_akses === 'user');
    $isAdmin            = ($isNonIT && $user->user_akses === 'admin');
@endphp


<nav class="sidebar">
    <header>
        <div class="image-text">
            <span class="image"><img src="{{ asset('assets/img/logo/1.png') }}" alt=""></span>
            <div class="text logo-text"><h4 class="name">Inventory IT</h4></div>
        </div>
        <i class="bx bx-chevron-right toggle"></i>
    </header>
    <hr style="margin-top: 5px;">

    <div class="menu-bar">
            <ul class="bottom-content list-unstyled">
                @if($isDev || $isSuperAdmin || $isUserNonIT || $isAdmin)
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa fa-home icon"></i><span class="text nav-text">Home</span>
                    </a>
                </li>
                @endif
                
                @if($isDev || $isAdmin || $isSuperAdmin)
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center {{
                        request()->routeIs('plants.*') || request()->routeIs('departemens.*') || request()->routeIs('positions.*') || request()->routeIs('users.*') ||
                        request()->routeIs('tickets.*') || request()->routeIs('hardwares.*') || request()->routeIs('softwares.*') ? 'active' : ''
                    }}" href="#" id="masterDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-database icon"></i><span class="text nav-text">Master</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="masterDropdown">
                        @if($isDev || $isSuperAdmin)
                        <li><a class="dropdown-item {{ request()->routeIs('plants.index') ? 'active' : '' }}" href="{{ route('plants.index') }}">Plant</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('departemens.index') ? 'active' : '' }}" href="{{ route('departemens.index') }}">Departemen</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('positions.index') ? 'active' : '' }}" href="{{ route('positions.index') }}">Position</a></li>
                        @endif
                        @if($isDev || $isAdmin || $isSuperAdmin)
                        <li><a class="dropdown-item {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">User</a></li>
                        @endif
                        @if($isDev || $isSuperAdmin)
                        <li><a class="dropdown-item {{ request()->routeIs('hardwares.index') ? 'active' : '' }}" href="{{ route('hardwares.index') }}">Hardware</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('softwares.index') ? 'active' : '' }}" href="{{ route('softwares.index') }}">Software</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                
                @if($isDev || $isImplementator || $isAdmin)
                <li class="nav-item">
                    <a href="{{ route('userHirarkis.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('userHirarkis.index') ? 'active' : '' }}">
                        <i class="fa fa-user-cog icon"></i><span class="text nav-text">Setting Approval<br>Form Klaim</span>
                    </a>
                </li>
                @endif

                {{-- @if($isDev || $isUserNonIT || $isAdmin) --}}
                <li class="nav-item">
                    <a href="{{ route('ticketing.create_ticket') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.create_ticket') ? 'active' : '' }}">
                        <i class="fa fa-ticket icon"></i><span class="text nav-text">Create Ticket</span>
                    </a>
                </li>
                {{-- @endif --}} 
                
                @if($isDev || $isAdmin || $isUserNonIT)
                <li class="nav-item">
                    <a href="{{ route('ticketing.approval') }}"
                    class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.approval') ? 'active' : '' }}">
                        <i class="fa fa-clipboard-check icon"></i>
                        <span class="text nav-text">Approval</span>
                    </a>
                </li>
                @endif

                @if($isDev || $isAdmin || $isUserNonIT)
                <li class="nav-item">
                    <a href="{{ route('ticketing.user_confirm_hardware') }}"
                    class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.user_confirm_hardware') ? 'active' : '' }}">
                        <i class="fa fa-user-check icon"></i>
                        <span class="text nav-text">User Confirm PPK</span>
                    </a>
                </li>
                @endif

                            
                @if($isDev || $isAdmin || $isUserNonIT )
                   <li class="nav-item">
                    <a href="{{ route('ticketing.track_ticket') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.track_ticket') ? 'active' : '' }}">
                        <i class="fa fa-search icon"></i><span class="text nav-text">Track My Ticket</span>
                    </a>
                </li> 
                @endif
                
                @if($isDev || $isSuperAdmin || $isUserNonIT || $isAdmin)
                <li class="nav-item">
                    <a href="{{ route('ticketing.queue_ticket') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.queue_ticket') ? 'active' : '' }}">
                        <i class="fa fa-list icon"></i><span class="text nav-text">Ticket Queue</span>
                    </a>
                </li>
                @endif

                @if($isDev || $isImplementator || $isTS || $isAdminIt || $isAsmenIt)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('ticketing.incoming_software') || request()->routeIs('ticketing.incoming_hardware') ? 'active' : '' }}" href="#" id="incomingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-inbox icon"></i><span class="text nav-text">Incoming Ticket</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="incomingDropdown">
                        @if($isDev || $isLeaderImp || $isAdminIt || $isImplementator || $isAsmenIt)
                            <li><a class="dropdown-item {{ request()->routeIs('ticketing.incoming_software') ? 'active' : '' }}" href="{{ route('ticketing.incoming_software') }}">Software</a></li>
                        @endif
                        @if($isDev || $isLeaderTs || $isAdminIt || $isTS || $isAsmenIt)
                            <li><a class="dropdown-item {{ request()->routeIs('ticketing.incoming_hardware') ? 'active' : '' }}" href="{{ route('ticketing.incoming_hardware') }}">Hardware</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if($isDev || $isSuperAdmin)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('ticketing.report_ticket_software') || request()->routeIs('ticketing.report_ticket_hardware') ? 'active' : '' }}" href="#" id="reportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-chart-bar icon"></i><span class="text nav-text">Report</span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="reportDropdown">
                        @if($isDev || $isAsmenIt || $isLeaderImp || $isAdminIt || $isImplementator)
                        <li><a class="dropdown-item" href="{{ route('ticketing.report_ticket_software') }}">Software</a></li>
                        @endif
                        @if($isDev || $isAsmenIt || $isLeaderTs || $isAdminIt || $isTS)
                        <li><a class="dropdown-item" href="{{ route('ticketing.report_ticket_hardware') }}">Hardware</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if($isDev || $isAsmenIt || $isLeaderImp || $isLeaderTs)
                <li class="nav-item">
                    <a href="{{ route('ticketing.report_approval') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('ticketing.report_approval') ? 'active' : '' }}">
                        <i class="fa fa-list icon"></i><span class="text nav-text">Report Approval</span>
                    </a>
                </li>
                @endif

                <hr>

                <li class="nav-item mode mt-2">
                    <div class="sun-moon">
                        <i class="fa fa-moon icon moon"></i>
                        <i class="fa fa-sun icon sun"></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>
                    <div class="toggle-switch"><span class="switch"></span></div>
                </li>
            </ul>
    </div>
</nav>
