<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    // ── POST /webhook/midtrans ──────────────────────────────────────────
    // Called by Midtrans server — must be excluded from CSRF & auth!
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('MidtransWebhook: received', [
            'order_id' => $payload['order_id'] ?? '?',
            'status'   => $payload['transaction_status'] ?? '?',
        ]);

        try {
            $this->midtrans->handleWebhook($payload);
            return response('OK', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            Log::warning('MidtransWebhook: order not found', ['order_id' => $payload['order_id'] ?? '?']);
            return response('Order not found', 404);
        } catch (\Exception $e) {
            Log::error('MidtransWebhook: ' . $e->getMessage());
            return response($e->getMessage(), 400);
        }
    }
}
