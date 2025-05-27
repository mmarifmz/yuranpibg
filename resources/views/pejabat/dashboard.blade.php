@extends('components.layouts.app')

@section('content')
<!-- Bootstrap fixed-top navbar -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="{{ asset('storage/logo-ssp-167x168.png') }}" alt="SSP Logo" width="40" class="me-2">
            PIBG SK Sri Petaling 2025/2026
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/pejabat/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pejabat/status">Status by Kelas</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Push content below navbar -->
<div style="height: 80px;"></div>

<div class="container py-4">
    <div class="bg-light p-4 rounded shadow mb-5">
        <h2 class="text-center mb-4">🏆 Ringkasan Kutipan Yuran PIBG 2025/2026</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'Jumlah Keluarga'" :value="$familyCount" color="gray" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'Telah Bayar'" :value="$paidCount" color="green" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'Belum Bayar'" :value="$pendingCount" color="red" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'🎯 Sasaran Kutipan'" :value="'RM ' . number_format($targetAmount, 2)" color="blue" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'💰 Jumlah Terkumpul'" :value="'RM ' . number_format($totalCollected, 2)" color="yellow" />
                    <div class="text-sm text-center text-muted mt-2">
                        Yuran PIBG: RM {{ number_format($yuranTotal, 2) }}<br>
                        Sumbangan: RM {{ number_format($sumbanganTotal, 2) }}
                        <span class="text-xs">({{ $sumbanganFamilyCount }} keluarga)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-3 rounded shadow-sm">
                    <x-summary-box :title="'📊 Peratusan Kutipan'" :value="round(($totalCollected / $targetAmount) * 100, 1) . '%'" color="indigo" />
                </div>
            </div>
        </div>
    </div>

    <div class="bg-light p-4 rounded shadow">
        <h3 class="text-center mb-4">📚 Status Mengikut Kelas</h3>
        <canvas id="kelasChart"></canvas>
    </div>

    <div class="bg-light p-4 rounded shadow mt-5">
        <h3 class="text-center mb-4">📈 Jumlah Bayaran Mengikut Tarikh</h3>
        <canvas id="bayaranChart"></canvas>
    </div>
</div>

<footer style="text-align: center; font-size: 12px; color: #888; margin-top: 40px;">
    Sistem direka oleh <strong>Biro ICT, PIBG Sekolah Kebangsaan Sri Petaling</strong> 2025/2026 | Pembangun Sistem 
    <strong><a href="https://arif.my" target="_blank" style="color: #888; text-decoration: none;">Arif + Co.</a></strong>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('kelasChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($classBreakdown->pluck('class_name')),
            datasets: [
                {
                    label: 'Telah Bayar',
                    data: @json($classBreakdown->pluck('paid')),
                    backgroundColor: 'rgba(34, 197, 94, 0.7)'
                },
                {
                    label: 'Belum Bayar',
                    data: @json($classBreakdown->pluck('pending')),
                    backgroundColor: 'rgba(239, 68, 68, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const bayarCtx = document.getElementById('bayaranChart').getContext('2d');
    const bayaranChart = new Chart(bayarCtx, {
        type: 'bar',
        data: {
            labels: @json($chartDates),
            datasets: [{
                label: 'Jumlah Bayaran (RM)',
                data: @json($chartAmounts),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const value = context.dataset.data[index];
                            const families = @json($chartFamilies)[index];
                            return `RM ${value} (${families} keluarga)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Bayaran (RM)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tarikh Pembayaran'
                    }
                }
            }
        }
    });
</script>
@endsection

