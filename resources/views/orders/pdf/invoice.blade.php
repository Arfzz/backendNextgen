<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; line-height: 1.5; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: bottom; }
        .header-right { display: table-cell; text-align: right; vertical-align: bottom; }
        .title { font-size: 28px; font-weight: bold; margin: 0; color: #1e3a8a; }
        .info-section { display: table; width: 100%; margin-bottom: 30px; }
        .info-col { display: table-cell; width: 50%; }
        .info-col h4 { margin-top: 0; margin-bottom: 5px; color: #666; font-size: 14px; text-transform: uppercase; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th, table.items td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        table.items th { background-color: #f8fafc; font-weight: 600; color: #475569; }
        table.items .text-right { text-align: right; }
        .total-section { text-align: right; margin-top: 20px; font-size: 18px; font-weight: bold; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-failed { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1 class="title">INVOICE</h1>
                <p>Order ID: {{ $order->order_id }}</p>
            </div>
            <div class="header-right">
                <h2>NextGen Community</h2>
                <p>Tanggal: {{ $order->created_at ? $order->created_at->format('d F Y') : '-' }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="info-col">
                <h4>Ditagihkan Kepada:</h4>
                <p>
                    <strong>{{ $order->user_name }}</strong><br>
                    {{ $order->user_email }}
                </p>
            </div>
            <div class="info-col" style="text-align: right;">
                <h4>Status Pembayaran:</h4>
                <p>
                    @if($order->status == 'paid')
                        <span class="status-badge status-paid">LUNAS</span>
                    @elseif($order->status == 'pending')
                        <span class="status-badge status-pending">MENUNGGU PEMBAYARAN</span>
                    @else
                        <span class="status-badge status-failed">{{ strtoupper($order->status) }}</span>
                    @endif
                </p>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Paket Beasiswa: <strong>{{ $order->package_name }}</strong></td>
                    <td class="text-right">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            Total Pembayaran: Rp {{ number_format($order->amount, 0, ',', '.') }}
        </div>

        <div class="footer">
            Terima kasih telah bergabung dengan NextGen Community.
        </div>
    </div>
</body>
</html>
