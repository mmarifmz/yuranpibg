@extends('components.layouts.app')

@section('content')
<div class="container py-5">
    <div class="text-center mb-3">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" alt="Logo Sekolah" style="height: 100px;">
    </div>
    <h2 class="mb-4 text-center">
        📄 Resit Rasmi PIBG 2025/2026<br>
        <small class="d-block text-muted">Sekolah Kebangsaan Sri Petaling</small>
    </h2>

    <div class="bg-white rounded shadow-sm p-4">
        <p><strong>Nama Murid:</strong> {{ $studentName }}</p>
        <p><strong>Family ID:</strong> {{ $familyId }}</p>
        <p><strong>Tarikh Bayaran:</strong>
            {{ $paidAt ? \Carbon\Carbon::parse($paidAt)->format('d/m/Y h:i A') : '-' }}
        </p>
        <p><strong>Status:</strong> <span class="badge bg-success">Telah Bayar</span></p>
        <p><strong>Jumlah Dibayar:</strong> RM {{ number_format($amountPaid, 2) }}</p>
        <p><strong>No Transaksi:</strong> {{ $paymentRef }}</p>
    </div>

    <div class="mt-4 text-center">
        <p class="text-muted small">Resit ini dijana secara automatik dan tidak memerlukan tandatangan.</p>
        <p class="text-muted">Terima kasih atas sumbangan anda kepada PIBG sekolah.</p>
    </div>
</div>
@endsection
