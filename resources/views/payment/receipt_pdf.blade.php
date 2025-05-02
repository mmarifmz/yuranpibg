<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resit PIBG PDF</title>
    <style>
        body {
            font-family: sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info {
            margin-bottom: 15px;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ public_path('storage/logo-ssp-167x168.png') }}" alt="Logo Sekolah" style="height: 80px;">
    </div>
    <div class="header">
        <h2>RESIT RASMI PIBG 2025</h2>
        <p>SEKOLAH KEBANGSAAN SRI PETALING</p>
    </div>

    <div class="info"><span class="bold">Nama Murid:</span> {{ $studentName }}</div>
    <div class="info"><span class="bold">Family ID:</span> {{ $familyId }}</div>
    <div class="info"><span class="bold">Tarikh:</span> {{ $transaction->created_at->format('d/m/Y') }}</div>
    <div class="info"><span class="bold">Status:</span> Telah Bayar</div>
    <div class="info"><span class="bold">Jumlah:</span> RM {{ number_format($transaction->amount, 2) }}</div>
    <div class="info"><span class="bold">Transaksi:</span> {{ $transaction->transaction_id }}</div>

    <p style="margin-top: 30px;">Resit ini dijana secara automatik. Tidak memerlukan tandatangan.<br>
    Terima kasih atas sumbangan anda kepada PIBG sekolah.</p>
</body>
</html>