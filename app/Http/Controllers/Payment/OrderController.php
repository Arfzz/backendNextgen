<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    // ── POST /api/v1/orders ─────────────────────────────────────────────
    // Flutter calls this when user clicks "Beli"
    public function create(Request $request): JsonResponse
    {
        $request->validate(['package_id' => 'required|string']);

        $user = $request->user();

        // ── Guard: student may not purchase while already enrolled ──────
        // Normalize beasiswa_diampu — may be a JSON-encoded string in MongoDB
        $rawBeasiswa = $user->getRawOriginal('beasiswa_diampu') ?? $user->beasiswa_diampu ?? [];
        if (is_string($rawBeasiswa) && str_starts_with(trim($rawBeasiswa), '[')) {
            $decoded = json_decode($rawBeasiswa, true);
            $rawBeasiswa = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($rawBeasiswa)) {
            $rawBeasiswa = [];
        }

        if (! empty($rawBeasiswa)) {
            return response()->json([
                'message' => 'Kamu tidak dapat membeli paket beasiswa lain saat sedang aktif mengampu beasiswa. Selesaikan program saat ini terlebih dahulu.',
            ], 422);
        }
        // ────────────────────────────────────────────────────────────────

        try {
            $order = $this->midtrans->createTransaction(
                $user,
                $request->input('package_id')
            );

            return response()->json([
                'order_id'     => $order->order_id,
                'snap_token'   => $order->snap_token,
                'redirect_url' => $order->redirect_url,
                'amount'       => $order->amount,
                'status'       => $order->status,
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'Paket tidak ditemukan.'], 404);
        } catch (\Throwable $e) {
            Log::error('OrderController@create', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], 500);
        }
    }

    // ── GET /api/v1/orders/{orderId}/status ─────────────────────────────
    // Flutter polls this to check payment result
    public function status(Request $request, string $orderId): JsonResponse
    {
        $order = Order::where('order_id', $orderId)
            ->where('user_id', (string) $request->user()->getAuthIdentifier())
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        // Actively query Midtrans API when still pending.
        // This is essential for localhost dev where webhooks can't be delivered.
        if ($order->status === 'pending') {
            $order = $this->midtrans->checkTransactionStatus($order);
        }

        return response()->json([
            'order_id'     => $order->order_id,
            'status'       => $order->status,
            'amount'       => $order->amount,
            'package_name' => $order->package_name,
            'paid_at'      => $order->paid_at?->toIso8601String(),
        ]);
    }
}
