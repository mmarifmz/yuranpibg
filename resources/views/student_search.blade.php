<!-- resources/views/student_search.blade.php -->
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semak Yuran PIBG 2025/2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7fa; font-family: 'Segoe UI', sans-serif; }
        .status-badge.paid { background-color: #2d6a4f; }
        .status-badge.pending { background-color: #f4a261; }
        .scroll-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            display: none;
        }

        .card {
            border-radius: 0.75rem;
        }

        .card .card-header {
            background-color: #f8f9fa;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .card .card-body {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<div class="container mb-5">
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" width="80">

        <h5 class="fw-bold mb-4">PORTAL YURAN & SUMBANGAN PIBG<br>SEKOLAH KEBANGSAAN SRI PETALING</h5>

        <div id="portalIntro" class="collapse {{ !$searched ? 'show' : '' }}">
            <p class="text-center ">Selamat datang ke Portal Rasmi Yuran & Sumbangan PIBG Sekolah Kebangsaan Sri Petaling bagi sesi persekolahan 2025/2026.<br/>

            Portal ini dibangunkan untuk memudahkan ibu bapa dan penjaga membuat semakan dan pembayaran yuran serta sumbangan PIBG secara dalam talian, dengan pantas dan selamat.</p>

            <div class="container">
                <div class="row g-4">
                    <!-- Objektif Portal -->
                    <div class="col-md-4">
                        <div class="bg-light border rounded p-3 h-100 shadow-sm">
                            <h6 class="text-uppercase text-success fw-bold mb-3">Objektif Portal</h6>
                            <ul class="list-unstyled small text-start">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Semakan sumbangan PIBG bagi setiap keluarga</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Proses pembayaran dalam talian (FPX ToyyibPay)</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Resit digital yang mudah diakses</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Guru dapat pantau status sumbangan murid</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Ciri-Ciri Portal -->
                    <div class="col-md-4">
                        <div class="bg-light border rounded p-3 h-100 shadow-sm">
                            <h6 class="text-uppercase text-primary fw-bold mb-3">Ciri-ciri Portal</h6>
                            <ul class="list-unstyled small text-start">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Pendigitalan proses pembayaran yuran</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Paparan nama mengikut daftar keluarga</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Terima bayaran yuran serta sumbangan ikhlas</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Semakan semula resit pada bila-bila masa</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Sokongan & Bantuan -->
                    <div class="col-md-4">
                        <div class="bg-light border rounded p-3 h-100 shadow-sm">
                            <h6 class="text-uppercase text-warning fw-bold mb-3">Sokongan & Bantuan</h6>
                            <p class="small text-start mb-0">
                                Sekiranya terdapat sebarang masalah atau pertanyaan mengenai portal ini, sila hubungi:
                                <br>
                                <strong>AJK PIBG</strong> atau <strong>guru kelas anak anda</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($searched)
        <div class="text-center mb-4">
            <button class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#portalIntro">
                📘 Paparan Maklumat Portal
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('search.handle') }}" method="POST" class="row g-3 justify-content-center align-items-end">
                @csrf
                <div class="col-md-4">
                    <label for="student_name" class="form-label fw-semibold">🔍 Nama Pelajar</label>
                    <input type="text" name="student_name" id="student_name"
                           class="form-control form-control-lg"
                           placeholder="Contoh: Adam, Hawa"
                           value="{{ old('student_name', $student_name ?? '') }}" required>
                </div>
                <div class="col-md-3">
                    <label for="class_name" class="form-label fw-semibold">Kelas</label>
                    <select name="class_name" id="class_name" class="form-select form-select-lg">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classNames as $class)
                            <option value="{{ $class }}" @selected(($selectedClass ?? '') == $class)>{{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg me-md-2">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('search.form') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($families))
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($families as $familyId => $students)
                @php
                    $allPaid = $students->every(fn($s) => $s->payment_status === 'paid');
                    $anyPending = $students->contains(fn($s) => $s->payment_status === 'pending');
                @endphp
                <div class="col">
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            @php
                                $familyStatus = $students->every(fn($s) => $s->payment_status === 'paid') ? 'paid' : 'pending';
                            @endphp
                            <span class="badge status-badge {{ $familyStatus }}">{{ ucfirst($familyStatus) }}</span>
                            <small class="text-muted">ID: {{ $familyId }}</small>
                        </div>
                        <div class="card-body">
                            @foreach($students as $student)
                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <div class="text-truncate" style="max-width: 75%;">
                                        @php
                                            $name = strtoupper($student->student_name);
                                            $masked = strlen($name) > 12
                                                ? substr($name, 0, 6) . '***' . substr($name, -6)
                                                : $name;
                                        @endphp
                                        {{ $masked }}
                                    </div>
                                    <span class="badge bg-info">
                                        {{ $student->class_name }}
                                    </span>
                                </div>
                            @endforeach

                            @if($anyPending)
                                <div class="mt-3">
                                    <a href="{{ route('review.payment', ['familyId' => $familyId]) }}"
                                       class="btn btn-success w-100 rounded-pill fw-semibold">
                                        <i class="bi bi-cash-coin me-2"></i> Bayar Yuran & Sumbangan
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <button id="loadMore" class="btn btn-outline-secondary">Display More..</button>
        </div>
    @elseif(isset($searched))
        <div class="alert alert-warning text-center mt-4">Tiada pelajar dijumpai.</div>
    @endif
</div>

<a href="#" class="btn btn-outline-secondary position-fixed bottom-0 end-0 m-4 rounded-pill" id="scrollTopBtn">⬆ </a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll to top button visibility
    window.addEventListener('scroll', () => {
        document.getElementById('scrollTopBtn').style.display =
            window.scrollY > 300 ? 'block' : 'none';
    });

    // Scroll to top on click
    document.getElementById('scrollTopBtn').addEventListener('click', e => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Simulated lazy loading (replace with actual pagination if needed)
    const allCards = [...document.querySelectorAll('.col')];
    let visibleCount = 6;
    const loadMoreBtn = document.getElementById('loadMore');

    function renderLazyCards() {
        allCards.forEach((el, i) => {
            el.style.display = i < visibleCount ? 'block' : 'none';
        });
        if (visibleCount >= allCards.length) {
            loadMoreBtn.style.display = 'none';
        }
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            visibleCount += 6;
            renderLazyCards();
        });
        renderLazyCards();
    }
</script>
</body>
</html>