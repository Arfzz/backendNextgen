@extends('layouts.admin')

@section('title', 'Detail Order')

@section('content')

<div class="page-header" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('orders.index') }}"
       style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#F1F5F9;color:#475569;text-decoration:none;transition:background .2s;"
       onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
    </a>
    <div style="display:flex;align-items:center;gap:12px;flex:1;">
        <h1 class="page-title" style="margin:0;">Detail Order</h1>
        @if($order->status === 'paid')
        <a href="{{ route('orders.printInvoice', $order->_id) }}" target="_blank"
           style="padding:6px 12px;border:1px solid #0284C7;background:#0284C7;color:#fff;border-radius:6px;font-size:12px;text-decoration:none;display:flex;align-items:center;gap:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
            Invoice PDF
        </a>
        @endif
    </div>
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
    <span style="{{ $statusStyle }};padding:5px 14px;border-radius:9999px;font-size:13px;font-weight:600;">
        {{ ucfirst($order->status) }}
    </span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-top:8px;">

    {{-- ── Left: Order Detail ── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Order Info --}}
        <div class="data-card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#0F172A;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F1F5F9;">
                Informasi Order
            </div>
            <dl style="display:grid;grid-template-columns:160px 1fr;gap:10px 0;font-size:13px;margin:0;">
                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Order ID</dt>
                <dd style="font-family:monospace;font-weight:600;color:#334155;padding:6px 0;word-break:break-all;">
                    {{ $order->order_id }}
                </dd>

                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Paket Beasiswa</dt>
                <dd style="font-weight:700;color:#0F172A;font-size:14px;padding:6px 0;">
                    {{ $order->package_name }}
                </dd>

                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Jumlah Pembayaran</dt>
                <dd style="font-weight:700;color:#059669;font-size:16px;padding:6px 0;">
                    Rp {{ number_format($order->amount, 0, ',', '.') }}
                </dd>

                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Tanggal Order</dt>
                <dd style="color:#334155;padding:6px 0;">
                    {{ $order->created_at?->format('d M Y, H:i:s') }}
                </dd>

                @if($order->paid_at)
                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Waktu Dibayar</dt>
                <dd style="color:#059669;font-weight:600;padding:6px 0;">
                    {{ $order->paid_at->format('d M Y, H:i:s') }}
                </dd>
                @endif

                @if($order->snap_token)
                <dt style="color:#94A3B8;font-weight:500;padding:6px 0;">Snap Token</dt>
                <dd style="font-family:monospace;font-size:11px;color:#94A3B8;padding:6px 0;word-break:break-all;">
                    {{ $order->snap_token }}
                </dd>
                @endif
            </dl>
        </div>

        {{-- Midtrans Response (collapsible) --}}
        @if($order->midtrans_response)
        <div class="data-card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#0F172A;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #F1F5F9;">
                Midtrans Response
            </div>
            <pre style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;font-size:11px;overflow:auto;max-height:220px;margin:0;line-height:1.6;color:#334155;">{{ json_encode(json_decode($order->midtrans_response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

    </div>

    {{-- ── Right: Student Info + Status Control ── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Student Info --}}
        <div class="data-card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#0F172A;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Info Student
            </div>
            <dl style="display:grid;gap:8px;font-size:13px;margin:0;">
                <div>
                    <div style="color:#94A3B8;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Nama</div>
                    <div style="font-weight:600;color:#0F172A;">{{ $order->user_name }}</div>
                </div>
                <div>
                    <div style="color:#94A3B8;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Email</div>
                    <div style="color:#334155;">{{ $order->user_email }}</div>
                </div>
                <div>
                    <div style="color:#94A3B8;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">User ID</div>
                    <div style="font-family:monospace;font-size:11px;color:#94A3B8;word-break:break-all;">{{ $order->user_id }}</div>
                </div>
            </dl>
        </div>

        {{-- Status Control --}}
        <div class="data-card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#0F172A;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 015.07 19.07M4.93 4.93A10 10 0 0119.07 19.07"/>
                </svg>
                Ubah Status Order
            </div>

            <form method="POST" action="{{ route('orders.updateStatus', $order->_id) }}"
                  id="status-form">
                @csrf

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                        Status Baru
                    </label>
                    <select name="status" id="status-select"
                        style="width:100%;padding:9px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;">
                        @foreach(['pending','paid','failed','expired','cancelled'] as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Warning when paid is selected --}}
                <div id="paid-warning"
                     style="display:none;background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400E;margin-bottom:14px;line-height:1.5;">
                    ⚠️ Mengubah ke <strong>Paid</strong> akan otomatis menambahkan
                    <strong>{{ $order->package_name }}</strong>
                    ke daftar <code style="background:#FDE68A;padding:1px 4px;border-radius:3px;">beasiswa_diampu</code>
                    student <strong>{{ $order->user_name }}</strong>.
                </div>

                <button type="button" onclick="confirmStatusChange()"
                    class="btn btn-primary" style="width:100%;padding:10px;">
                    Simpan Perubahan
                </button>
            </form>
        </div>

    </div>
</div>

{{-- Confirm Modal --}}
<div class="modal-overlay" id="confirm-modal">
    <div class="modal-box">
        <div class="modal-icon" id="confirm-icon">⚙️</div>
        <h3 id="confirm-title">Ubah Status?</h3>
        <p id="confirm-msg">Yakin ingin mengubah status order ini?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('confirm-modal').classList.remove('active')">
                Batal
            </button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('status-form').submit()">
                Ya, Ubah
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const select = document.getElementById('status-select');
    const warning = document.getElementById('paid-warning');

    select.addEventListener('change', function () {
        warning.style.display = this.value === 'paid' ? 'block' : 'none';
    });

    // Trigger on load if current is paid
    if (select.value === 'paid') warning.style.display = 'block';

    function confirmStatusChange() {
        const newStatus = select.value;
        const icon  = newStatus === 'paid' ? '✅' : newStatus === 'failed' ? '❌' : '⚙️';
        const title = 'Ubah ke ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + '?';
        const msg   = newStatus === 'paid'
            ? 'Beasiswa akan otomatis diberikan ke student setelah konfirmasi.'
            : 'Yakin ingin mengubah status order ini?';

        document.getElementById('confirm-icon').textContent  = icon;
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-msg').textContent   = msg;
        document.getElementById('confirm-modal').classList.add('active');
    }

    document.getElementById('confirm-modal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
</script>
@endsection
