<!-- resources/views/student_search.blade.php -->
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semak Yuran PIBG 2025/2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" width="80">
        <h2 class="fw-bold mt-3">Semak Yuran PIBG 2025/2026</h2>
    </div>

    <form action="{{ route('search.handle') }}" method="POST" class="row g-3 mb-4 justify-content-center">
        @csrf
        <div class="col-md-4">
            <input type="text" name="student_name" class="form-control" placeholder="Cari nama pelajar"
                   value="{{ old('student_name', $student_name ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <select name="class_name" class="form-select">
                <option value="">-- Semua Kelas --</option>
                @foreach($classNames as $class)
                    <option value="{{ $class }}" @selected(($selectedClass ?? '') == $class)>{{ $class }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Cari</button>
        </div>
    </form>

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
                            <span class="badge bg-primary">{{ $students->first()->class_name }}</span>
                            <small class="text-muted">ID: {{ $familyId }}</small>
                        </div>
                        <div class="card-body">
                            @foreach($students as $student)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>{{ strtoupper($student->student_name) }}</div>
                                    <span class="badge status-badge {{ strtolower($student->payment_status) }}">
                                        {{ ucfirst($student->payment_status) }}
                                    </span>
                                </div>
                            @endforeach
                            @if($anyPending)
                                <div class="mt-3">
                                    <a href="{{ route('review.payment', $familyId) }}" class="btn btn-success w-100">
                                        Bayar Sekarang
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <button id="loadMore" class="btn btn-outline-secondary">Muat Lagi</button>
        </div>
    @elseif(isset($searched))
        <div class="alert alert-warning text-center mt-4">Tiada pelajar dijumpai.</div>
    @endif
</div>

<a href="#" class="btn btn-primary scroll-top" id="scrollTopBtn">⬆ Kembali atas</a>

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