@extends('components.layouts.app')

@section('content')
<div class="container py-5">
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" width="80">
        <h5 class="fw-bold mb-4">PORTAL YURAN & SUMBANGAN PIBG<br>SEKOLAH KEBANGSAAN SRI PETALING</h5>
    </div>

    <h3 class="text-danger text-center">❌ Transaksi Tidak Berjaya</h3>
    <p class="text-center">Sila semak maklumat transaksi di bawah dan cuba semula:</p>

    <div class="row mt-4">
        <div class="col-md-6">
            <h5>Maklumat Pembayar</h5>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><strong>ID Keluarga:</strong> {{ $family->family_id }}</li>
                <li class="list-group-item"><strong>Nama Murid:</strong> {{ $flow->bill_to ?? '-' }}</li>
                <li class="list-group-item"><strong>Email:</strong> {{ $flow->bill_email ?? '-' }}</li>
                <li class="list-group-item"><strong>No Telefon:</strong> {{ $flow->bill_phone ?? '-' }}</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h5>Butiran Transaksi</h5>
            <div class="p-3 bg-light border rounded">
                <p><strong>Status:</strong> {{ strtoupper($flow->status ?? '-') }}</p>
                <p><strong>Transaksi:</strong> {{ $flow->transaction_id ?? '-' }}</p>
                <p><strong>Nama Murid:</strong> {{ $flow->bill_to ?? '-' }}</p>
                <p><strong>BillCode:</strong> {{ $flow->bill_code ?? '-' }}</p>
                <p><strong>Jumlah:</strong> RM {{ number_format(($flow->bill_amount ?? 0) / 100, 2) }}</p>
                <p><strong>Dibatalkan Pada:</strong> {{ $flow->cancelled_at ? \Carbon\Carbon::parse($flow->cancelled_at)->format('d-m-Y') : '-' }}</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <form method="POST" action="{{ route('payment.retry', $family->family_id) }}">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-redo-alt"></i> Cuba Bayar Semula
            </button>
        </form>
    </div>
</div>
@endsection