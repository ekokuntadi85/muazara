<!DOCTYPE html>
<html>
<head>
    <title>Receipt #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: monospace; /* Use monospace for thermal printer feel */
            font-size: 10px;
            width: 80mm; /* Typical thermal printer width */
            margin: 0; /* Remove default margins */
            padding: 5mm; /* Small padding */
        }
        .container {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .details,
        .items,
        .total {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .items th,
        .items td,
        .total td {
            padding: 2px 0; /* Minimal padding */
            border: none; /* No borders for thermal look */
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>APOTEK MUAZARA</h3>
            <p>Desa Cermee RT. 15 No.1 Bondowoso</p>
        </div>

        <div class="line"></div>

        <table class="details">
            <tr>
                <td>Invoice:</td>
                <td class="text-right">{{ $transaction->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal:</td>
                <td class="text-right">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table class="items">
            <tbody>
                @foreach($transaction->transactionDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="text-right">{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right">Rp {{ number_format($detail->quantity * $detail->price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <table class="total">
            <tr>
                <td>Total:</td>
                <td class="text-right">Rp {{ number_format($transaction->total_price, 0) }}</td>
            </tr>
            <tr>
                <td>Bayar:</td>
                <td class="text-right">Rp {{ number_format($transaction->amount_paid, 0) }}</td>
            </tr>
            <tr>
                <td>Kembali:</td>
                <td class="text-right">Rp {{ number_format($transaction->change, 0) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="text-center" style="margin-top: 10px;">
            <p>Terima kasih!</p>
        </div>
    </div>
</body>
</html>