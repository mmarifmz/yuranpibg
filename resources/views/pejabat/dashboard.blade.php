@extends('components.layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-2xl font-bold mb-4">📊 Ringkasan Kutipan PIBG 2025/2026</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white shadow rounded-lg p-4 text-center">
            <h4 class="text-sm font-semibold text-gray-500">Jumlah Keluarga</h4>
            <p class="text-2xl font-bold text-indigo-700">{{ $familyCount }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 text-center">
            <h4 class="text-sm font-semibold text-gray-500">Status: Telah Bayar</h4>
            <p class="text-2xl font-bold text-green-600">{{ $paidCount }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 text-center">
            <h4 class="text-sm font-semibold text-gray-500">Status: Belum Bayar</h4>
            <p class="text-2xl font-bold text-red-500">{{ $pendingCount }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 text-center">
            <h4 class="text-sm font-semibold text-gray-500">🎯 Sasaran Kutipan</h4>
            <p class="text-2xl font-bold text-blue-500">RM {{ number_format($targetAmount, 2) }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 text-center col-span-1 md:col-span-2">
            <h4 class="text-sm font-semibold text-gray-500">💰 Jumlah Terkumpul</h4>
            <p class="text-3xl font-bold text-emerald-600">RM {{ number_format($totalCollected, 2) }}</p>
        </div>
    </div>
    <h3 class="text-xl font-semibold mt-8 mb-4">📚 Status Mengikut Kelas</h3>
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700">
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
    </div>

</div>
<footer style="text-align: center; font-size: 12px; color: #888; margin-top: 40px;">
    Sistem direka oleh <strong>Biro ICT, PIBG Sekolah Kebangsaan Sri Petaling</strong> 2025/2026 | Pembangun Sistem 
    <strong><a href="https://arif.my" target="_blank" style="color: #888; text-decoration: none;">Arif + Co.</a></strong>
</footer>
@endsection
