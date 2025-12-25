@extends('layout')

@section('content')
    <style>
        a { text-decoration: none; }

.dashboard-card {
    position: relative;
    border: none;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(0,0,0,.08);
    transition: all .35s ease;
    overflow: hidden;
}

.dashboard-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 5px;
    width: 100%;
}

.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 36px rgba(0,0,0,.15);
}

.card-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-title {
    font-size: .85rem;
    letter-spacing: .6px;
    font-weight: 600;
    text-transform: uppercase;
}

.card-value {
    font-size: 2.5rem;
    font-weight: 700;
}

.icon-box {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

/* ===== WARNA PER KATEGORI ===== */
.global::before { background: linear-gradient(90deg,#0d6efd,#6ea8fe); }
.software::before { background: linear-gradient(90deg,#20c997,#63e6be); }
.infra::before { background: linear-gradient(90deg,#fd7e14,#ffb366); }

.global .icon-box { background: rgba(13,110,253,.12); color:#0d6efd; }
.software .icon-box { background: rgba(32,201,151,.15); color:#20c997; }
.infra .icon-box { background: rgba(253,126,20,.15); color:#fd7e14; }

    </style>

    <div class="row g-3">

    <!-- GLOBAL -->
    <div class="col-12">
        <h5 class="fw-bold text-primary mb-1">Global Ticket</h5>
    </div>

    <div class="col-12">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card global">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Total Ticket</div>
                                    <div class="card-value">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-ticket"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card global">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Solved</div>
                                    <div class="card-value text-success">20</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-circle-check"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card global">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">On Progress</div>
                                    <div class="card-value text-warning">30</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-spinner"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- SOFTWARE -->
    <div class="col-12">
        <h5 class="fw-bold text-success mb-1">Software Ticket</h5>
    </div>

    <div class="col-12">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card software">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Total</div>
                                    <div class="card-value">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-laptop-code"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card software">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Running</div>
                                    <div class="card-value text-warning">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-spinner"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card software">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Solved</div>
                                    <div class="card-value text-success">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-check"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- INFRA -->
    <div class="col-12">
        <h5 class="fw-bold text-warning mb-1">Infrastructure Ticket</h5>
    </div>

    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card infra">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Total</div>
                                    <div class="card-value">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-network-wired"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card infra">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Running</div>
                                    <div class="card-value text-warning">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-spinner"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="#">
                    <div class="card dashboard-card infra">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="card-title text-muted">Solved</div>
                                    <div class="card-value text-success">10</div>
                                </div>
                                <div class="icon-box"><i class="fa-solid fa-check"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
