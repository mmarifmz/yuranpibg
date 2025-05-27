<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran PIBG</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div style="height: 80px;"></div>

    <div class="container py-5">
        <h2 class="mb-4">Status Pembayaran PIBG Mengikut Kelas</h2>
        <small>
            <div class="mb-3 d-flex flex-wrap gap-2">
                <span class="badge bg-success">
                    ✅ {{ $greenCount }} kelas (80% – 100% Bayar)
                </span>
                <span class="badge bg-warning text-dark">
                    ⚠️ {{ $yellowCount }} kelas (50% – 79% Bayar)
                </span>
                <span class="badge bg-danger">
                    ❌ {{ $redCount }} kelas (Bawah 50% Bayar)
                </span>
            </div>
        </small>

        <ul class="list-group">
            @foreach ($classes as $class)
                @php
                    $data = $classStats[$class];
                    $percent = $data['percent'];
                    $paid = $data['paid'];
                    $total = $data['total'];

                    if ($percent >= 80) {
                        $color = 'bg-success';
                    } elseif ($percent >= 50) {
                        $color = 'bg-warning';
                    } else {
                        $color = 'bg-danger';
                    }
                @endphp

                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $class }}</strong>
                        <a href="{{ route('pejabat.class.status', ['className' => $class]) }}" class="btn btn-sm btn-primary">Lihat Senarai</a>
                    </div>

                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar {{ $color }}"
                             role="progressbar"
                             style="width: {{ $percent }}%;"
                             aria-valuenow="{{ $percent }}"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             data-bs-toggle="tooltip"
                             data-bs-placement="top"
                             title="{{ $paid }} / {{ $total }} pelajar dah bayar">
                            {{ $percent }}%
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div id="result" class="mt-5" style="display: none;">
            <h4 id="classTitle"></h4>

            <div class="mt-3">
                <h5>✅ Dah Bayar</h5>
                <ul id="paidList" class="list-group"></ul>
            </div>

            <div class="mt-3">
                <h5>❌ Belum Bayar</h5>
                <ul id="pendingList" class="list-group"></ul>
            </div>

            <p class="mt-4">
                <strong>Sila buat bayaran di:</strong><br>
                <a href="https://yuranpibg.sripetaling.edu.my/" target="_blank">
                    https://yuranpibg.sripetaling.edu.my/
                </a>
            </p>

            <button id="whatsappBtn" class="btn btn-success mt-3">Hantar ke WhatsApp</button>
        </div>
    </div>

    <script>
    function fetchStatus(className) {
        fetch(`/pejabat/status/${encodeURIComponent(className)}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').style.display = 'block';
                document.getElementById('classTitle').innerText = 'Kelas: ' + data.class_name;

                const paidList = document.getElementById('paidList');
                const pendingList = document.getElementById('pendingList');

                paidList.innerHTML = '';
                pendingList.innerHTML = '';

                let paidText = '';
                let pendingText = '';

                data.paid.forEach(name => {
                    let li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = name;
                    paidList.appendChild(li);
                    paidText += `- ${name}\n`;
                });

                data.pending.forEach(name => {
                    let li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = name;
                    pendingList.appendChild(li);
                    pendingText += `- ${name}\n`;
                });

                const message = 
                    `📄 *Status Pembayaran PIBG*
                    *Kelas: ${className}*

                    🟢 *Dah Bayar:*
                    ${paidList.map(n => '• ' + n).join('\n') || '• Tiada'}

                    🔴 *Belum Bayar:*
                    ${pendingList.map(n => '• ' + n).join('\n') || '• Tiada'}

                    Sila buat bayaran di:
                    https://yuranpibg.sripetaling.edu.my/`;

                document.getElementById('whatsappBtn').onclick = () => {
                    const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
                    window.open(url, '_blank');
                };
            });
    }
    </script>
    <footer style="text-align: center; font-size: 12px; color: #888; margin-top: 40px;">
    Sistem direka oleh <strong>Biro ICT, PIBG Sekolah Kebangsaan Sri Petaling</strong> 2025/2026 | Pembangun Sistem 
    <strong><a href="https://arif.my" target="_blank" style="color: #888; text-decoration: none;">Arif + Co.</a></strong>
</footer>
</body>
</html>