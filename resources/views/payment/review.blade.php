
@extends('components.layouts.app')
<body onload="updateTotal()">
@section('content')
<div class="container py-5">
    <h4>📄 <strong>Semak & Sahkan Pembayaran Yuran & Sumbangan PIBG 2025/2026</strong></h4>

    <div class="mt-3">
        <p><strong>📌 Keluarga ID:</strong> {{ $familyId }}</p>
        <p><strong>👪 Jumlah Anak:</strong> {{ $students->count() }}</p>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    @foreach ($students as $student)
        <div class="col">
            <div class="card shadow-sm border border-secondary-subtle h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-primary text-uppercase">{{ $student->class_name }}</span>
                    </div>
                    <div class="my-3">
                        <i class="bi bi-person fs-1 text-secondary"></i>
                    </div>
                    <h5 class="fw-bold">
                        {{ Str::limit($student->student_name, 5, ' .................. ') . substr($student->student_name, -5) }}
                    </h5>
                    <div class="mt-2">
                        <a href="#" onclick="alert('{{ $student->student_name }}')" class="btn btn-outline-primary btn-sm">
                            👁️ Lihat Nama Penuh
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <hr class="my-4">

    <form action="{{ route('confirm.payment', ['familyId' => $familyId]) }}" method="POST" id="paymentForm">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="bg-light p-4 rounded border mb-4">
            <h5>🧾 <strong>Itemized Bill</strong></h5>
            <div class="form-check my-2">
                <input class="form-check-input" type="checkbox" checked disabled>
                <label class="form-check-label">Yuran PIBG 2025/2026: <strong>RM 100.00</strong></label>
            </div>

            <label class="form-label fw-semibold mt-3">➕ Sumbangan Ikhlas PIBG (optional):</label>
            <div class="btn-group mb-2" role="group">
                <button class="btn btn-outline-secondary" type="button" onclick="setIkhlas(20)">RM20</button>
                <button class="btn btn-outline-secondary" type="button" onclick="setIkhlas(50)">RM50</button>
                <button class="btn btn-outline-secondary" type="button" onclick="setIkhlas(100)">RM100</button>
                <button class="btn btn-outline-secondary" type="button" onclick="setIkhlas(150)">RM150</button>
            </div>
            <div class="input-group">
                <span class="input-group-text">RM</span>
                <input type="number" name="donation_amount" id="donation_amount" class="form-control" value="0" oninput="updateTotal()">
                <input type="hidden" name="total_amount" id="total_amount" value="100">
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">📧 Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-4">
            <label for="phone" class="form-label">📱 No Telefon *</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <h5 class="mb-3">💰 Jumlah Bayaran: <strong>RM <span id="totalAmount">100</span>.00</strong></h5>
        <button type="submit" class="btn btn-success w-100">✅ Sahkan & Bayar</button>
    </form>
</div>
@endsection

@section('scripts')
<script>

    const baseFee = 100;
    const donationInput = document.getElementById('donation_amount');
    const totalDisplay = document.getElementById('totalAmount');
    
    function setIkhlas(amount) {
        donationInput.value = amount;
        updateTotal();
    }

    function updateTotal() {
        const donation = parseFloat(donationInput.value || 0);
        const total = baseFee + donation;
        totalDisplay.innerText = total;
        document.getElementById('total_amount').value = total;
    }

    donationInput.addEventListener('input', updateTotal);
</script>
@endsection