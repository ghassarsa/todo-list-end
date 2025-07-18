<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 40px;
            color: #333;
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .text-right {
            text-align: right;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>INVOICE</h1>
    <h2>{{ $invoice_number }}</h2>
    
    <div class="info">
        <p><strong>Tanggal :</strong> {{ $date }}</p>
        <p><strong>Status Pembayaran</strong> {{ ucfirst($transaction_status) }}</p>
        <p><strong>Nama :</strong> {{ $user_name }}</p>
        <p><strong>Email</strong> {{ $user_email }}</p>
        <p><strong>ID Order :</strong> {{--{{ $order_id }}--}}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $plan_name }}</td>
                <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                <td class="text-right">1</td>
                <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <tr colspan="3" class="text-right">Total :</tr>
                <tr class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</tr>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Terima kasih telah berinteraksi dengan kami.</p>
        <p>Invoice ini dibuat secara otomatis dan tidak memerlukan tanda tangan.</p>
    </div>
</body>
</html>