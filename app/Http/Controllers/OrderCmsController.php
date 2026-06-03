<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaketBeasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderCmsController extends Controller
{
    // ── GET /orders ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $status  = $request->input('status');
        $search  = $request->input('search');

        $query = Order::orderBy('created_at', 'desc');

        if ($status) $query->where('status', $status);
        if ($search) $query->where(function ($q) use ($search) {
            $q->where('order_id', 'regex', "/{$search}/i")
              ->orWhere('user_name', 'regex', "/{$search}/i")
              ->orWhere('package_name', 'regex', "/{$search}/i");
        });

        $orders = $query->paginate(20);

        $stats = [
            'total'   => Order::count(),
            'paid'    => Order::where('status', 'paid')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'failed'  => Order::whereIn('status', ['failed', 'expired'])->count(),
            'revenue' => Order::where('status', 'paid')->sum('amount'),
        ];

        return view('orders.index', compact('orders', 'stats', 'status', 'search'));
    }

    // ── GET /orders/{id} ────────────────────────────────────────────────
    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return view('orders.show', compact('order'));
    }

    // ── POST /orders/{id}/status ─────────────────────────────────────────
    // Admin manually sets status — if set to 'paid', grants beasiswa access
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,expired,cancelled',
        ]);

        $order     = Order::findOrFail($id);
        $newStatus = $request->input('status');
        $oldStatus = $order->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $updateData['paid_at'] = now();
            $this->grantBeasiswaAccess($order);
        }

        $order->update($updateData);

        Log::info('OrderCmsController: status updated by admin', [
            'order_id'   => $order->order_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return redirect()
            ->route('orders.show', $id)
            ->with('success', "Status berhasil diubah ke «{$newStatus}»." .
                ($newStatus === 'paid' ? ' Beasiswa sudah diberikan ke student.' : ''));
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function grantBeasiswaAccess(Order $order): void
    {
        $user = User::find($order->user_id);
        if (! $user) {
            Log::warning('OrderCmsController: user not found', ['user_id' => $order->user_id]);
            return;
        }

        $package = PaketBeasiswa::find($order->package_id);
        if (! $package) {
            Log::warning('OrderCmsController: package not found', ['package_id' => $order->package_id]);
            return;
        }

        $beasiswaName    = $package->nama_beasiswa;
        $currentBeasiswa = is_array($user->beasiswa_diampu)
            ? $user->beasiswa_diampu
            : (json_decode($user->beasiswa_diampu ?? '[]', true) ?? []);

        if (! in_array($beasiswaName, $currentBeasiswa)) {
            $currentBeasiswa[] = $beasiswaName;
            $user->update(['beasiswa_diampu' => array_values($currentBeasiswa)]);

            Log::info('OrderCmsController: beasiswa granted', [
                'user_id'      => $order->user_id,
                'user_name'    => $order->user_name,
                'beasiswa'     => $beasiswaName,
            ]);
        } else {
            Log::info('OrderCmsController: beasiswa already exists, skip', [
                'user_id'  => $order->user_id,
                'beasiswa' => $beasiswaName,
            ]);
        }
    }
    // ── GET /orders/export-pdf ──────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $status  = $request->input('status');
        $search  = $request->input('search');

        $query = Order::orderBy('created_at', 'desc');

        if ($status) $query->where('status', $status);
        if ($search) $query->where(function ($q) use ($search) {
            $q->where('order_id', 'regex', "/{$search}/i")
              ->orWhere('user_name', 'regex', "/{$search}/i")
              ->orWhere('package_name', 'regex', "/{$search}/i");
        });

        $orders = $query->get();
        $stats = [
            'total'   => Order::count(),
            'paid'    => Order::where('status', 'paid')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'revenue' => Order::where('status', 'paid')->sum('amount'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.report', compact('orders', 'stats', 'status', 'search'));
        return $pdf->download('laporan-pembayaran.pdf');
    }

    // ── GET /orders/{id}/invoice ────────────────────────────────────────
    public function printInvoice(string $id)
    {
        $order = Order::findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.invoice', compact('order'));
        return $pdf->download('invoice-' . $order->order_id . '.pdf');
    }
}
