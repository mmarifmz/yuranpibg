@extends('components.layouts.app')

<body onload="updateTotal()">
@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="text-center mb-4">
                <img src="{{ asset('storage/logo-ssp-167x168.png') }}" width="80">

                <h5 class="fw-bold mb-4">PORTAL YURAN & SUMBANGAN PIBG<br>SEKOLAH KEBANGSAAN SRI PETALING</h5>
            </div>

            <div class="container-fluid px-2 px-md-3 py-4">
              <div class="row g-1">
                <div class="col-lg-5">
                  <div class="bg-success text-white rounded p-4 h-100 shadow animate__animated animate__fadeInLeft">
                    <h4 class="fw-bold">Bil Bayaran</h4>
                    <hr class="border-white">
                    <p class="mb-1">Yuran PIBG 2025/2026</p>
                    <h3 class="fw-bold">RM 100.00</h3>
                    <p class="mt-4 mb-1">📌 Siri Keluarga:</p>
                    <p class="fw-semibold">SSP/{{ $familyId }}</p>
                    <p class="mb-1">👪 Jumlah Anak:</p>
                    <p class="fw-semibold">{{ $students->count() }}</p>
                    <div class="mt-4">
                      <p class="fw-bold">Senarai Anak:</p>
                      <ul class="ps-3 list-unstyled">
                        @foreach (
                          $students->sortBy(function($s) {
                            $kelas = $s->class_name;
                            if (stripos($kelas, 'PRASEKOLAH') !== false) return 999;
                            if (preg_match('/^6/', $kelas)) return 1;
                            if (preg_match('/^5/', $kelas)) return 2;
                            if (preg_match('/^4/', $kelas)) return 3;
                            if (preg_match('/^3/', $kelas)) return 4;
                            if (preg_match('/^2/', $kelas)) return 5;
                            if (preg_match('/^1/', $kelas)) return 6;
                            return 100;
                          }) as $student)
                          @php
                            $kelas = $student->class_name;
                            $badgeClass = 'bg-secondary';
                            $icon = '🎓';
                            if (stripos($kelas, 'PRASEKOLAH') !== false) {
                              $badgeClass = 'bg-warning text-dark';
                              $icon = '🧒';
                            } elseif (preg_match('/^6/', $kelas)) {
                              $badgeClass = 'bg-danger';
                            } elseif (preg_match('/^5/', $kelas)) {
                              $badgeClass = 'bg-primary';
                            } elseif (preg_match('/^4/', $kelas)) {
                              $badgeClass = 'bg-info';
                            } elseif (preg_match('/^3/', $kelas)) {
                              $badgeClass = 'bg-warning-subtle text-dark';
                            } elseif (preg_match('/^2/', $kelas)) {
                              $badgeClass = 'bg-dark';
                            } elseif (preg_match('/^1/', $kelas)) {
                              $badgeClass = 'bg-light text-dark';
                            }
                          @endphp
                          <li class="mb-2">
                            <span class="fs-6"> {{ $student->student_name }}</span>
                            <span class="badge {{ $badgeClass }} ms-2 fs-6 bg-">{{ $icon }} {{ strtoupper($student->class_name) }}</span>
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-7">
                  <div class="card shadow-sm border-0 overflow-hidden animate__animated animate__fadeInRight">
                    <div class="card-body">
                      <h5 class="fw-bold mb-4">Maklumat Pembayaran</h5>

                      <!-- Secure Payment Banner -->
                      <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                        <div>
                          Pembayaran anda dijamin selamat dan akan diproses melalui platform Toyyibpay dengan pilihan <strong>FPX Online Banking</strong> yang dipercayai.
                        </div>
                      </div>

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

                        <div class="mb-3">
                          <label class="form-label">Sumbangan Ikhlas PIBG</label>
                          <div class="d-flex flex-wrap gap-2 mb-2">
                            <button class="btn btn-outline-success" type="button" onclick="setIkhlas(20)">RM20</button>
                            <button class="btn btn-outline-success" type="button" onclick="setIkhlas(50)">RM50</button>
                            <button class="btn btn-outline-success" type="button" onclick="setIkhlas(100)">RM100</button>
                            <button class="btn btn-outline-success" type="button" onclick="setIkhlas(150)">RM150</button>
                          </div>
                          <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="donation_amount" id="donation_amount" class="form-control" value="0" oninput="updateTotal()">
                            <input type="hidden" name="total_amount" id="total_amount" value="100">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label for="email" class="form-label"><i class="bi bi-envelope-fill"></i> Email *</label>
                          <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-4">
                          <label for="phone" class="form-label"><i class="bi bi-telephone-fill"></i> No Telefon *</label>
                          <input type="text" name="phone" class="form-control" required>
                        </div>

                        <div class="bg-light p-3 rounded mb-4 text-end">
                          <h5 class="mb-0">Jumlah Bayaran: <strong>RM <span id="totalAmount">100.00</span></strong></h5>
                        </div>

                        <div class="d-grid">
                         <button type="submit" class="btn btn-success btn-lg w-100">Sahkan & Bayar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
<!-- Floating Back to Portal Button -->
<a href="/" class="btn btn-outline-secondary position-fixed top-0 end-0 m-4 rounded-pill">
  <i class="bi bi-house-door-fill"></i> 
</a>
@endsection

@section('scripts')
<!-- Animate.css for transitions -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
    totalDisplay.innerText = total.toFixed(2);
    document.getElementById('total_amount').value = total;
  }

  donationInput.addEventListener('input', updateTotal);
</script>
@endsection