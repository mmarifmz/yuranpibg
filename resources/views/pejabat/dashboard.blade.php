<!-- resources/views/pejabat/dashboard.blade.php -->
@extends('components.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-gray-100 p-6 rounded-2xl shadow mb-8">
        <h2 class="text-2xl font-bold mb-4">🏋️ Ringkasan Kutipan Yuran PIBG 2025/2026</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <x-summary-box :title="'Jumlah Keluarga'" :value="$familyCount" color="gray" />
                <x-summary-box :title="'Telah Bayar'" :value="$paidCount" color="green" />
                <x-summary-box :title="'Belum Bayar'" :value="$pendingCount" color="red" />
                <x-summary-box :title="'🎯 Sasaran Kutipan'" :value="'RM ' . number_format($targetAmount, 2)" color="blue" />
                <x-summary-box :title="'💰 Jumlah Terkumpul'" :value="'RM ' . number_format($totalCollected, 2)" color="yellow" />
            </div>
    </div>
    <br/>
    <div class="bg-gray-100 p-6 rounded-2xl shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">📚 Status Mengikut Kelas</h2>
            <!--select id="filterKelas" class="border border-gray-300 rounded px-3 py-1 text-sm">
                <option value="">Semua Tahap</option>
                <option value="1">Tahun 1</option>
                <option value="2">Tahun 2</option>
                <option value="3">Tahun 3</option>
                <option value="4">Tahun 4</option>
                <option value="5">Tahun 5</option>
                <option value="6">Tahun 6</option>
            </select-->
        </div>

        <canvas id="kelasChart" class="mb-6"></canvas>

        <!--div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left bg-white rounded-xl">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-2">Kelas</th>
                        <th class="px-4 py-2 text-center">Jumlah</th>
                        <th class="px-4 py-2 text-green-700 text-center">Telah Bayar</th>
                        <th class="px-4 py-2 text-red-700 text-center">Belum Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classBreakdown as $row)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold">{{ strtoupper($row->class_name) }}</td>
                            <td class="px-4 py-2 text-center">{{ $row->total }}</td>
                            <td class="px-4 py-2 text-center text-green-600 font-medium">{{ $row->paid }}</td>
                            <td class="px-4 py-2 text-center text-red-600 font-medium">{{ $row->pending }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div-->
    </div>
</div>

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
                    backgroundColor: 'rgba(34, 197, 94, 0.7)' // green
                },
                {
                    label: 'Belum Bayar',
                    data: @json($classBreakdown->pluck('pending')),
                    backgroundColor: 'rgba(239, 68, 68, 0.7)' // red
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

    /*document.getElementById('filterKelas').addEventListener('change', function () {
        const tahap = this.value;
        window.location.href = tahap ? `?tahap=${tahap}` : window.location.pathname;
    });*/
</script>
@endsection