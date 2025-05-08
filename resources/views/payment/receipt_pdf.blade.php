<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resit Yuran PIBG 2025/2026</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            width: 80px;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .info {
            margin: 10px 0;
            font-size: 14px;
        }

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
        }

        .watermark {
            position: absolute;
            top: 30%;
            left: 20%;
            opacity: 0.06;
            z-index: 0;
        }

        .watermark img {
            width: 300px;
        }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="{{ public_path('storage/logo-ssp-167x168.png') }}" alt="Logo Watermark">
    </div>

    <div class="header" style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Resit Rasmi PIBG</h2>
        <p style="font-weight: bold;">Persatuan Ibu Bapa & Guru SK Sri Petaling</p>
    </div>

    <div style="text-align: right; margin-top: -40px; margin-right: 10px;">
        <img src="{{ public_path('storage/logo-ssp-167x168.png') }}" style="width: 100px; opacity: 0.1;">
    </div>

    <div class="info"><span class="bold">Nama Murid:</span> {{ $studentName }}</div>
    <div class="info"><span class="bold">Siri Keluarga:</span> SSP/{{ $familyId }}</div>
    <div class="info"><span class="bold">Tarikh Bayaran:</span> {{ $transaction->created_at->format('d-m-Y') }}</div>
    <div class="info"><span class="bold">Status:</span> Telah Bayar</div>
    <div class="info"><span class="bold">Jumlah:</span> RM {{ number_format($transaction->bill_amount/100 ?? 0, 2) }}</div>
    <div class="info"><span class="bold">Ref. Number:</span> {{ $transaction->transaction_id }}</div>

    <p class="footer">Resit ini dijana secara automatik. Tidak memerlukan tandatangan.<br>
    Terima kasih atas sumbangan anda kepada PIBG sekolah.</p>
</body>
</html>