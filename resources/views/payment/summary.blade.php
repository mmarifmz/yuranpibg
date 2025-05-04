@extends('components.layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="text-danger">❌ Transaksi Tidak Berjaya</h3>
    <p>Sila semak maklumat di bawah dan cuba semula:</p>

    <ul class="list-group list-group-flush mb-4">
        <li class="list-group-item"><strong>ID Keluarga:</strong> {{ $family->family_id ?? '-' }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ strtoupper($flow->status ?? 'UNKNOWN') }}</li>
        <li class="list-group-item"><strong>Transaksi:</strong> {{ $flow->transaction_id ?? '-' }}</li>
        <li class="list-group-item">
            <strong>Jumlah:</strong> 
            RM {{ isset($flow->amount) ? number_format($flow->amount / 100, 2) : '0.00' }}
        </li>
    </ul>

    @if ($flow && $flow->bill_code && $flow->status !== 'paid')
        {{-- Legacy retry using original bill link --}}
        <a href="https://toyyibpay.com/{{ $flow->bill_code }}" class="btn btn-outline-secondary me-3">
            🔁 Guna Bil Sedia Ada
        </a>
    @endif

    @if ($flow && $flow->status !== 'paid')
        {{-- Safer Retry: regenerate new bill --}}
        <form action="{{ route('payment.retry', $family->family_id) }}" method="POST" class="d-inline-block">
            @csrf
            <input type="hidden" name="email" value="{{ $flow->bill_email ?? 'unknown@domain.com' }}">
            <input type="hidden" name="phone" value="{{ $flow->bill_phone ?? '' }}">
            <button type="submit" class="btn btn-danger">
                🔄 Cuba Bayar Semula
            </button>
        </form>
    @endif
</div>
@endsection