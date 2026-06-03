@extends('layouts.admin')

@section('title', 'Manajemen Pembayaran')

@section('content')

<div class="page-header">
    <h1 class="page-title">Manajemen Pembayaran</h1>
</div>

{{-- ── Stats Cards ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">

    <div style="background:#fff;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.08);display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#0F172A;line-height:1;">{{ number_format($stats['total']) }}</div>
            <div style="font-size:12px;color:#64748B;margin-top:2px;">Total Order</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.08);display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#059669;line-height:1;">{{ number_format($stats['paid']) }}</div>
            <div style="font-size:12px;color:#64748B;margin-top:2px;">Berhasil Dibayar</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.08);display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#D97706;line-height:1;">{{ number_format($stats['pending']) }}</div>
            <div style="font-size:12px;color:#64748B;margin-top:2px;">Menunggu</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.08);display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
            <div style="font-size:18px;font-weight:700;color:#059669;line-height:1;">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
            <div style="font-size:12px;color:#64748B;margin-top:2px;">Total Pendapatan</div>
        </div>
    </div>

</div>

{{-- ── Toolbar: Search + Filter ── --}}
<div class="toolbar" style="gap:12px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('orders.index') }}" x-data="{ search: '{{ $search ?? '' }}' }"
          style="display:flex;align-items:center;gap:10px;flex:1;flex-wrap:wrap;">

        <div class="search-box" style="flex:1;min-width:200px;">
            <span class="search-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari order ID / nama / paket…"
                   x-model="search" @input.debounce.500ms="$el.form.submit()">
        </div>

        <select name="status"
            style="padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;">
            <option value="">Semua Status</option>
            @foreach(['pending','paid','failed','expired','cancelled'] as $s)
                <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>

        <button type="submit" style="padding:8px 18px;border:none;background:#0EA5E9;color:#fff;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:6px;cursor:pointer;">
            Filter
        </button>
        @if($search || $status)
        <a href="{{ route('orders.index') }}"
           style="padding:8px 14px;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#64748B;text-decoration:none;background:#fff;">
            Reset
        </a>
        @endif
        
        <a href="{{ route('orders.exportPdf', request()->all()) }}" target="_blank"
           style="padding:8px 18px;border:none;background:#E10000;color:#fff;border-radius:8px;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
            Export PDF
        </a>
    </form>
</div>

{{-- ── Table ── --}}
<div class="data-card">
    @if($orders->count() > 0)
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Student</th>
                    <th>Paket Beasiswa</th>
                    <th style="text-align:right">Jumlah</th>
                    <th style="text-align:center">Status</th>
                    <th>Tanggal</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:11px;color:#64748B;">
                            {{ $order->order_id }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#0F172A;font-size:13px;">{{ $order->user_name }}</div>
                        <div style="font-size:11px;color:#94A3B8;">{{ $order->user_email }}</div>
                    </td>
                    <td style="font-weight:600;font-size:13px;">{{ $order->package_name }}</td>
                    <td style="text-align:right;font-weight:700;font-size:13px;color:#059669;">
                        Rp {{ number_format($order->amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align:center;">
                        @php
                            $statusStyle = match($order->status) {
                                'paid'      => 'background:#D1FAE5;color:#065F46',
                                'pending'   => 'background:#FEF3C7;color:#92400E',
                                'failed'    => 'background:#FEE2E2;color:#991B1B',
                                'expired'   => 'background:#F1F5F9;color:#475569',
                                'cancelled' => 'background:#F1F5F9;color:#475569',
                                default     => 'background:#F1F5F9;color:#475569',
                            };
                        @endphp
                        <span style="{{ $statusStyle }};padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:600;">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:#64748B;">
                        {{ $order->created_at?->format('d M Y') }}<br>
                        <span style="color:#94A3B8;">{{ $order->created_at?->format('H:i') }}</span>
                    </td>
                    <td style="text-align:center;">
                        <div class="action-buttons">
                            <a href="{{ route('orders.show', $order->_id) }}"
                               class="action-btn view" title="Lihat Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #F1F5F9;">
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif

    @else
    <div class="empty-state">
        <div class="empty-icon">💳</div>
        <p>Belum ada data order pembayaran.</p>
    </div>
    @endif
</div>

@endsection
