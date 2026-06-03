<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; padding: 0; }
        .stats { margin-top: 20px; margin-bottom: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
        .stats div { margin-bottom: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Pembayaran NextGen</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
    </div>

    <div class="stats">
        <div><strong>Total Order:</strong> {{ number_format($stats['total']) }}</div>
        <div><strong>Berhasil Dibayar:</strong> {{ number_format($stats['paid']) }}</div>
        <div><strong>Total Pendapatan:</strong> Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order ID</th>
                <th>Siswa</th>
                <th>Paket Beasiswa</th>
                <th class="text-right">Jumlah (Rp)</th>
                <th class="text-center">Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order->order_id }}</td>
                    <td>{{ $order->user_name }}<br><small>{{ $order->user_email }}</small></td>
                    <td>{{ $order->package_name }}</td>
                    <td class="text-right">{{ number_format($order->amount, 0, ',', '.') }}</td>
                    <td class="text-center">{{ ucfirst($order->status) }}</td>
                    <td>{{ $order->created_at ? $order->created_at->format('d M Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
