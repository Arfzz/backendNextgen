@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard Overview</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-icon beasiswa">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div class="stat-details">
                <div class="stat-title">Total Beasiswa</div>
                <div class="stat-value">{{ $totalBeasiswa ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon mentor">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div class="stat-details">
                <div class="stat-title">Total Mentor</div>
                <div class="stat-value">{{ $totalMentor ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon artikel">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="stat-details">
                <div class="stat-title">Total Artikel</div>
                <div class="stat-value">{{ $totalArtikel ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Dashboard Charts Container --}}
    <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-top: 32px;">

        <!-- ============================================== -->
        <!-- CHART 1: TOTAL PENJUALAN 2026 (LINE CHART) -->
        <!-- ============================================== -->
        <div class="chart-card dashboard-chart" id="chart1" style="background: white; padding: 24px; border-radius: 24px; box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="color: #132440; font-size: 18px; font-family: 'Poppins', sans-serif; font-weight: 500;">Total Penjualan</div>
                <select id="filterPenjualan" style="padding: 4px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="2026">Tahun 2026</option>
                    <option value="2025">Tahun 2025</option>
                    <option value="q1">Q1 2026</option>
                </select>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="totalPenjualanChart"></canvas>
            </div>
        </div>
        <!-- ============================================== -->

        <!-- ============================================== -->
        <!-- CHART 2: TOP PAKET BEASISWA (BAR CHART)  -->
        <!-- ============================================== -->
        <div class="chart-card dashboard-chart" id="chart2" style="background: white; padding: 24px; border-radius: 24px; box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="color: #132440; font-size: 18px; font-family: 'Poppins', sans-serif; font-weight: 500;">Top Paket Beasiswa</div>
                <select id="filterTopBeasiswa" style="padding: 4px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="3">Top 3</option>
                    <option value="5">Top 5</option>
                    <option value="7">Top 7</option>
                </select>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="topBeasiswaChart"></canvas>
            </div>
        </div>
        <!-- ============================================== -->

        <!-- ============================================== -->
        <!-- CHART 3: MENTOR VS PESERTA (PIE CHART)     -->
        <!-- ============================================== -->
        <div class="chart-card dashboard-chart" id="chart3" style="background: white; padding: 24px; border-radius: 24px; box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="color: #132440; font-size: 18px; font-family: 'Poppins', sans-serif; font-weight: 500;">Mentor vs Peserta</div>
                <select id="filterPieFormat" style="padding: 4px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="angka">Angka (Raw)</option>
                    <option value="persen">Persentase (%)</option>
                </select>
            </div>
            <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                <canvas id="mentorVsPesertaChart"></canvas>
            </div>
        </div>
        <!-- ============================================== -->

        <!-- ============================================== -->
        <!-- CHART 4: PERBANDINGAN PENDAPATAN (AREA CHART)-->
        <!-- ============================================== -->
        <div class="chart-card dashboard-chart" id="chart4" style="background: white; padding: 24px; border-radius: 24px; box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="color: #132440; font-size: 18px; font-family: 'Poppins', sans-serif; font-weight: 500;">Perbandingan Pendapatan</div>
                <select id="filterPendapatan" style="padding: 4px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="yearly">Per Tahun</option>
                    <option value="monthly">Per Bulan</option>
                </select>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="totalPendapatanChart"></canvas>
            </div>
        </div>
        <!-- ============================================== -->

    </div>
@endsection

@section('scripts')
{{-- Include libraries --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Anime.js Staggering Animation for Chart Cards layout
        anime({
            targets: '.dashboard-chart',
            translateY: [50, 0],
            opacity: [0, 1],
            delay: anime.stagger(150),
            easing: 'easeOutElastic(1, .8)',
            duration: 1000
        });

        // Reusable function to configure grid options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: "'Inter', sans-serif", size: 12 },
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            }
        };

        const fetchChartData = async (endpoint) => {
            const res = await fetch(`/api/dashboard/charts/${endpoint}`);
            return await res.json();
        }

        // 1. Total Penjualan Chart (Interactive)
        let totalPenjualanChartInstance = null;
        const loadTotalPenjualan = (filterValue = '2026') => {
            fetchChartData(`total-penjualan?filter=${filterValue}`).then(res => {
                const ctx = document.getElementById('totalPenjualanChart').getContext('2d');
                if (totalPenjualanChartInstance) {
                    totalPenjualanChartInstance.destroy();
                }

                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(137, 121, 255, 0.3)'); 
                gradient.addColorStop(1, 'rgba(137, 121, 255, 0.0)'); 

                totalPenjualanChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: res.data,
                            borderColor: res.borderColor,
                            backgroundColor: gradient,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: res.borderColor,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            pointRadius: 4,
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: { beginAtZero: true, grid: { drawBorder: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        };

        // Load initially
        loadTotalPenjualan();

        // Listen for filter changes
        document.getElementById('filterPenjualan').addEventListener('change', (e) => {
            loadTotalPenjualan(e.target.value);
        });

        // 2. Top Paket Beasiswa (Interactive)
        let topBeasiswaChartInstance = null;
        const loadTopBeasiswa = (limitValue = '3') => {
            fetchChartData(`top-beasiswa?limit=${limitValue}`).then(res => {
                const ctx = document.getElementById('topBeasiswaChart').getContext('2d');
                if (topBeasiswaChartInstance) {
                    topBeasiswaChartInstance.destroy();
                }

                topBeasiswaChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            label: 'Jumlah',
                            data: res.data,
                            backgroundColor: res.backgroundColor,
                            borderRadius: { topLeft: 4, topRight: 4 },
                            barPercentage: 0.4
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: { beginAtZero: true, grid: { drawBorder: false } },
                            x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45 } }
                        }
                    }
                });
            });
        };

        loadTopBeasiswa();

        document.getElementById('filterTopBeasiswa').addEventListener('change', (e) => {
            loadTopBeasiswa(e.target.value);
        });

        // 3. Mentor vs Peserta (Interactive)
        let mentorVsPesertaChartInstance = null;
        const loadMentorVsPeserta = (formatValue = 'angka') => {
            fetchChartData('mentor-vs-peserta').then(res => {
                const ctx = document.getElementById('mentorVsPesertaChart').getContext('2d');
                if (mentorVsPesertaChartInstance) {
                    mentorVsPesertaChartInstance.destroy();
                }

                mentorVsPesertaChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: res.labels,
                        datasets: [{
                            data: res.data,
                            backgroundColor: res.backgroundColor,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { family: "'Inter', sans-serif", size: 12 }, usePointStyle: true, boxWidth: 10 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw || 0;
                                        if (formatValue === 'persen') {
                                            let sum = 0;
                                            let dataArr = context.chart.data.datasets[0].data;
                                            dataArr.map(data => { sum += data; });
                                            let percentage = ((value * 100) / sum).toFixed(2) + '%';
                                            return `${label}: ${percentage}`;
                                        }
                                        return `${label}: ${value}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        };

        loadMentorVsPeserta();

        document.getElementById('filterPieFormat').addEventListener('change', (e) => {
            loadMentorVsPeserta(e.target.value);
        });

        // 4. Perbandingan Pendapatan (Area Chart - Interactive)
        let totalPendapatanChartInstance = null;
        const loadTotalPendapatan = (filterValue = 'yearly') => {
            fetchChartData(`total-pendapatan?filter=${filterValue}`).then(res => {
                const ctx = document.getElementById('totalPendapatanChart').getContext('2d');
                if (totalPendapatanChartInstance) {
                    totalPendapatanChartInstance.destroy();
                }

                // Append fill property to datasets for Area Chart effect
                res.datasets.forEach(dataset => {
                    dataset.fill = true;
                    dataset.borderWidth = 2;
                    dataset.tension = 0.4;
                    dataset.pointBackgroundColor = dataset.borderColor;
                    dataset.pointBorderColor = '#fff';
                    dataset.pointBorderWidth = 1;
                    dataset.pointRadius = 4;
                });

                totalPendapatanChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: res.labels,
                        datasets: res.datasets
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { drawBorder: false },
                                ticks: {
                                    callback: function(value) {
                                        return value >= 1000 ? (value/1000) + 'k' : value;
                                    }
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        };

        // Load initially
        loadTotalPendapatan();

        // Listen for filter changes
        document.getElementById('filterPendapatan').addEventListener('change', (e) => {
            loadTotalPendapatan(e.target.value);
        });

    });
</script>
@endsection
