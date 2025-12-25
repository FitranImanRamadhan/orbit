@extends('layout')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header text-center fw-bold text-primary py-2">REPORT DIAGRAM INFRASTRUKTUR</div>
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="card h-100 border">
                        <div class="card-body d-flex flex-column py-1 px-2">
                            <div class="small fw-semibold text-center mb-1">Infrastruktur Tickets All Plant</div>
                            <canvas id="problemChartBar" height="510"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body py-1 px-1">
                                    <div class="small fw-semibold text-center mb-0">Department Tickets</div>
                                    <canvas id="problemChartPie" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body py-1 px-1">
                                    <div class="small fw-semibold text-center mb-0">Jenis Problem</div>
                                    <canvas id="problemChartDoughnut" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card border">
                        <div class="card-body py-1 px-2">
                            <div class="small fw-semibold text-center mb-1">Ticket Infrastruktur per Plant</div>
                            <canvas id="problemChartPoint" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        let barChart, doughnutChart, pointChart, pieChart;

        function getParam(name) {
            return new URLSearchParams(window.location.search).get(name);
        }

        $(document).ready(function() {
            loadData();
        });

        function loadData() {
            $.ajax({
                url: '/ticketings/report/data_chart_ticket_software',
                type: 'POST',
                data: {
                    year: getParam('year'),
                    month: getParam('month'),
                    week: getParam('week')
                },
                success: function(res) {
                    if (res.success) {
                        if (res.success) {
                            renderBar(res.bar);
                            renderDonut(res.donut);
                            renderPie(res.pie);
                            renderLine(res.line);
                        }
                    }
                }
            });
        }

        const problemChartBar = document.getElementById('problemChartBar');
        const problemChartDoughnut = document.getElementById('problemChartDoughnut');
        const problemChartPoint = document.getElementById('problemChartPoint');
        const problemChartPie = document.getElementById('problemChartPie');

        function renderBar(bar) {
            const labels = ['Solved', 'Unsolved'];
            const data = [bar.solved ?? 0, bar.unsolved ?? 0];
            if (barChart) barChart.destroy();
            barChart = new Chart(problemChartBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ticket',
                        data: data,
                        backgroundColor: ['#198754', '#dc3545']
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        function renderPie(pie) {

            const labels = pie.map(dept => dept.nama_departemen);
            const data = pie.map(dept => dept.total);

            if (pieChart) pieChart.destroy();
            pieChart = new Chart(problemChartPie, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545']
                    }]
                }
            });
        }

        function renderDonut(donut) {
            const labels = ['Manpower', 'Hardware', 'Network', 'Software'];
            const data = [
                donut.sum_manpower ?? 0,
                donut.sum_hardware ?? 0,
                donut.sum_network ?? 0,
                donut.sum_software ?? 0
            ];
            if (doughnutChart) doughnutChart.destroy();
            doughnutChart = new Chart(problemChartDoughnut, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#0d6efd', '#dc3545', '#198754', '#ffc107']
                    }]
                }
            });
        }

        function renderLine(line) {
            const labels = line.map(plant => plant.nama_plant);
            const data = line.map(plant => plant.total);

            if (pointChart) pointChart.destroy();
            pointChart = new Chart(problemChartPoint, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Ticket',
                        data: data,
                        borderColor: '#0d6efd',
                        pointRadius: 6,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
