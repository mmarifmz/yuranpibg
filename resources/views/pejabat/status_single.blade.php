 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran PIBG - {{ $class_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Status Pembayaran PIBG - {{ $class_name }}</h2>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <button id="whatsappBtn" class="btn btn-success mt-3">Hantar ke WhatsApp</button>
        <a href="{{ route('pejabat.status') }}" class="btn btn-secondary mt-3 ms-2">← Kembali ke Senarai Kelas</a>
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

    <p class="mt-3">
        <strong>Sila buat bayaran di:</strong><br>
        <a href="https://yuranpibg.sripetaling.edu.my/" target="_blank">https://yuranpibg.sripetaling.edu.my/</a>
    </p>

    
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

    const message = [
        `*Status Pembayaran PIBG* (${percentPaid}%)`,
        `*Kelas: ${className}*`,
        '',
        '*PAID*',
        paidList.length
            ? paidList.map(n => '• ' + toTitleCase(n)).join('\n')
            : '• Tiada',
        '',
        '*NOT PAID YET*',
        pendingList.length
            ? pendingList.map(n => '• ' + toTitleCase(n)).join('\n')
            : '• Tiada',
        '',
        'Sila buat bayaran di:',
        'https://yuranpibg.sripetaling.edu.my/'
    ].join('\n');

    document.getElementById('whatsappBtn').addEventListener('click', function () {
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    });
};
</script>
</body>
</html>