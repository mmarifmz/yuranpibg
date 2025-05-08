@extends('components.layouts.app')

@section('content')
<style>
    .receipt-container {
        max-width: 800px;
        margin: auto;
        padding: 40px;
        background: white;
        position: relative;
        border: 1px solid #ccc;
        font-family: "Courier New", Courier, monospace;
    }
    .receipt-container::before {
        content: "";
        background: url('{{ asset('images/ssp-logo.png') }}') no-repeat center;
        background-size: 200px;
        opacity: 0.05;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        height: 400px;
        z-index: 0;
    }
    .receipt-content {
        position: relative;
        z-index: 1;
    }
    .label {
        font-weight: bold;
    }
    .value {
        border-bottom: 1px solid #999;
        display: inline-block;
        min-width: 400px;
    }
</style>

<div class="container py-5">
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" width="80">
        <h5 class="fw-bold mb-4">PORTAL YURAN & SUMBANGAN PIBG<br>SEKOLAH KEBANGSAAN SRI PETALING</h5>
    </div>

    <div class="mb-4 border-success-subtle text-center">
        <h2 class="mb-3">✅ Terima kasih!</h2>
        <p class="fs-5">Bayaran Yuran PIBG untuk keluarga <strong>SSP/{{ $family->family_id }}</strong> telah berjaya.</p>
    </div>

    <div class="row justify-content-center mb-4"> 
        <div class="receipt-container shadow receipt-content">
            <h4 class="text-center mb-5">Official Receipt / Resit Rasmi<br/><span class="text-secondary">Persatuan IbuBapa & Guru <br/>Sekolah Kebangsaan Sri Petaling</span></h4>

            <p><span class="label">Date / Tarikh:</span> <span class="value">{{ \Carbon\Carbon::parse($flow->paid_at)->format('d-m-Y') }}</span></p>
            <p><span class="label">Receipt No:</span> <span class="value">{{ $flow->transaction_id }}</span></p>

            <p><span class="label">Received from / Diterima dari:</span> <span class="value">{{ $flow->bill_to }}</span></p>
            <p><span class="label">The Sum of Ringgit / Wang yang diterima:</span> 
                <span class="value">RM {{ number_format($flow->bill_amount / 100, 2) }}</span></p>

            <p><span class="label">In payment of / Untuk bayaran:</span> 
                <span class="value">Yuran PIBG 2025/2026</span></p>

            <p class="mt-5">
                <span class="label">Issued by / Yang menerima:</span> 
                <span class="value">PIBG Sekolah Kebangsaan Sri Petaling</span>
            </p>
            <p class="footer">Resit ini dijana secara automatik. Tidak memerlukan tandatangan.<br>
    Terima kasih atas sumbangan anda kepada PIBG sekolah.</p>
        </div>
        <div class="col-md-8 mt-4 text-center">
            <a 
                href="https://api.whatsapp.com/send?text={{ urlencode('Terima kasih! Bayaran PIBG keluarga SSP/' . $family->family_id . ' telah berjaya. Lihat resit rasmi di sini: ' . url()->current()) }}"
                target="_blank"
                class="btn btn-success mb-2"
            >
                📤 Kongsi Resit ke WhatsApp
            </a>
            <a href="{{ route('download.receipt', $family->family_id) }}" class="btn btn-success mb-2">
                ⬇️ Muat Turun Resit (PDF)
            </a>
            <a href="/" class="btn btn-success mb-2">
                🏠 Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</div>
@endsection