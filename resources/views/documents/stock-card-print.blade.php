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
                <th>Tipe</th>
                <th>Kuantitas</th>
                <th>Catatan</th>
                <th>Batch</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentBalance = $initialBalance;
            @endphp
            @forelse($finalMovements as $movement)
                @php
                    $currentBalance += $movement['quantity'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement['created_at'])->format('d M Y H:i') }}</td>
                    <td>{{ $movement['type'] }}</td>
                    <td>{{ $movement['quantity'] }}</td>
                    <td>{{ $movement['remarks'] }}</td>
                    <td>{{ $movement['batch_number'] ?? '-' }}</td>
                    <td>{{ $currentBalance }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada pergerakan stok dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>