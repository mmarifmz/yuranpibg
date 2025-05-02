@extends('components.layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-success text-center mb-4">✅ Terima kasih!</h2>

    <div class="text-center mb-3">
        <p class="fs-5">Bayaran Yuran PIBG untuk keluarga <strong>{{ $family->family_id }}</strong> telah berjaya.</p>
        <p><strong>Transaction ID:</strong> {{ $transactionId }}</p>
        <p><strong>Jumlah Dibayar:</strong> RM {{ number_format($amount / 100, 2) }}</p>
    </div>

    <hr>

    <h5 class="mt-4">👨‍👩‍👧‍👦 Senarai Anak:</h5>
    <ul>
        @foreach ($students as $student)
            <li>{{ $student->student_name }} ({{ $student->class_name }})</li>
        @endforeach
    </ul>

    <div class="text-center mt-4">
        <a href="{{ route('receipt.web', $family->family_id) }}" class="btn btn-success mb-2">
            📄 Lihat Resit Online
        </a>
        <br>
        <a href="{{ route('download.receipt', $family->family_id) }}" class="btn btn-success mb-2">
            ⬇️ Muat Turun Resit (PDF)
        </a>
        <br>
        <a href="{{ url('/') }}" class="btn btn-success">
            🏡 Kembali ke Halaman Utama
        </a>
    </div>
</div>
@endsection