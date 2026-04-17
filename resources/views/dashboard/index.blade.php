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

    {{-- Chart Card --}}
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">Revenue Per Bulan</div>
            <div>
                <select class="form-control" style="width: auto; display: inline-block;">
                    <option>Tahun Ini (2026)</option>
                </select>
            </div>
        </div>
        <div class="chart-container" style="position: relative; height:350px; width:100%">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
{{-- Include Chart.js from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Data injected from Controller
        const rawLabels = {!! json_encode($chartData['labels']) !!};
        const rawData = {!! json_encode($chartData['data']) !!};

        // Create a custom gradient for the line chart fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(1, 97, 120, 0.5)'); // Teal with opacity
        gradient.addColorStop(1, 'rgba(1, 97, 120, 0.0)'); // Fades out completely

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: rawLabels,
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: rawData,
                    borderColor: '#016178', // Line color
                    backgroundColor: gradient, // Fill color area
                    borderWidth: 3,
                    tension: 0.4, // Curveness of the line
                    fill: true,
                    pointBackgroundColor: '#F2BC45',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Hide default legend to match Figma clean look
                    },
                    tooltip: {
                        backgroundColor: '#132440',
                        titleFont: { family: "'Poppins', sans-serif", size: 13 },
                        bodyFont: { family: "'Poppins', sans-serif", size: 14, weight: 'bold' },
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false,
                        },
                        ticks: {
                            font: { family: "'Poppins', sans-serif" },
                            callback: function(value) {
                                return value >= 1000 ? (value/1000) + 'k' : value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            font: { family: "'Poppins', sans-serif" }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
