<!DOCTYPE html>
<html>
<head>
    <title>Kartu Stok {{ $product->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Kartu Stok Produk</h2>
        <h3>{{ $product->name }} (SKU: {{ $product->sku }})</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <p><strong>Saldo Awal (s/d {{ \Carbon\Carbon::parse($startDate)->subDay()->format('d M Y') }}):</strong> {{ $initialBalance }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Keterangan</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentBalance = $initialBalance;
            @endphp
            @forelse($finalMovements as $movement)
                @php
                    $currentBalance += $movement['masuk'] - $movement['keluar'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement['created_at'])->format('d/m/y') }}</td>
                    <td>{{ $movement['type'] }}</td>
                    <td>{{ $movement['remarks'] }}</td>
                    <td>{{ $movement['masuk'] > 0 ? $movement['masuk'] : '-' }}</td>
                    <td>{{ $movement['keluar'] > 0 ? $movement['keluar'] : '-' }}</td>
                    <td>{{ $currentBalance }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada pergerakan stok dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p><strong>Saldo Akhir:</strong> {{ $currentBalance }}</p>
</body>
</html>
