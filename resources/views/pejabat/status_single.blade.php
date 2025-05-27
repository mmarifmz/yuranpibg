 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran PIBG - {{ $class_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="{{ asset('storage/logo-ssp-167x168.png') }}" alt="SSP Logo" width="40" class="me-2">
            PIBG SK Sri Petaling 2025/2026
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/pejabat/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pejabat/status">Status by Kelas</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Push content below navbar -->
<div style="height: 20px;"></div>

<div class="container py-5">
    <h2 class="mb-4">Status Pembayaran Yuran PIBG - {{ $class_name }}</h2>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <button id="waPaidBtn" class="btn btn-success">
            <i class="fab fa-whatsapp"></i> List Bayar
        </button>

        <button id="waPendingBtn" class="btn btn-danger">
            <i class="fab fa-whatsapp"></i> List Belum Bayar
        </button>

        <button id="waFullBtn" class="btn btn-primary">
            <i class="fab fa-whatsapp"></i> Keseluruhan
        </button>
        <a href="{{ route('pejabat.status') }}" class="btn btn-secondary">← Senarai Kelas</a>
    </div>

    <div class="mb-4">
        <h5>✅ Dah Bayar</h5>
        <ul class="list-group">
            @forelse($paid as $student)
                <li class="list-group-item">{{ Str::title($student->student_name) }}</li>
            @empty
                <li class="list-group-item">Tiada rekod</li>
            @endforelse
        </ul>
    </div>

    <div class="mb-4">
        <h5>❌ Belum Bayar</h5>
        <ul class="list-group">
            @forelse($pending as $student)
                <li class="list-group-item">{{ Str::title($student->student_name) }}</li>
            @empty
                <li class="list-group-item">Tiada rekod</li>
            @endforelse
        </ul>
    </div>
    
</div>

<script>
function toTitleCase(str) {
    return str.toLowerCase().replace(/(^|\s)\S/g, t => t.toUpperCase());
}

window.onload = function () {
    const className = @json($class_name);
    const paidList = @json($paid->pluck('student_name'));
    const pendingList = @json($pending->pluck('student_name'));

    const total = paidList.length + pendingList.length;
    const percentPaid = total > 0 ? Math.round((paidList.length / total) * 100) : 0;

    const paidText = paidList.length
        ? paidList.map(n => '• ' + toTitleCase(n)).join('\n')
        : '• Tiada';

    const pendingText = pendingList.length
        ? pendingList.map(n => '• ' + toTitleCase(n)).join('\n')
        : '• Tiada';

    const fullMessage = [
        `*Status Pembayaran Yuran PIBG* (${percentPaid}%)`,
        `*Kelas: ${className}*`,
        '',
        '*PAID*',
        paidText,
        '',
        '*NOT PAID YET*',
        pendingText,
        '',
        'Pembayaran yuran kini online:',
        'https://yuranpibg.sripetaling.edu.my/'
    ].join('\n');

    const paidMessage = [
        `*Senarai Bayaran Yuran PIBG* (${percentPaid}%)`,
        `*Kelas: ${className}*`,
        '',
        '*PAID*',
        paidText
    ].join('\n');

    const pendingMessage = [
        `*Senarai Belum Bayar Yuran PIBG*`,
        `*Kelas: ${className}*`,
        '',
        '*NOT PAID YET*',
        pendingText,
        '',
        'Mohon ibubapa yang belum membuat bayaran untuk segera berbuat demikian melalui portal yuran pibg https://yuranpibg.sripetaling.edu.my/'
    ].join('\n');

    document.getElementById('waPaidBtn').onclick = () => {
        window.open(`https://wa.me/?text=${encodeURIComponent(paidMessage)}`, '_blank');
    };

    document.getElementById('waPendingBtn').onclick = () => {
        window.open(`https://wa.me/?text=${encodeURIComponent(pendingMessage)}`, '_blank');
    };

    document.getElementById('waFullBtn').onclick = () => {
        window.open(`https://wa.me/?text=${encodeURIComponent(fullMessage)}`, '_blank');
    };
};
</script>
<footer style="text-align: center; font-size: 12px; color: #888; margin-top: 40px;">
    Sistem direka oleh <strong>Biro ICT, PIBG Sekolah Kebangsaan Sri Petaling</strong> 2025/2026 | Pembangun Sistem 
    <strong><a href="https://arif.my" target="_blank" style="color: #888; text-decoration: none;">Arif + Co.</a></strong>
</footer>
</body>
</html>