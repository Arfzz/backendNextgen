<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaketBeasiswa;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        $serverKey    = config('midtrans.server_key');
        $isProduction = filter_var(config('midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);

        MidtransConfig::$serverKey    = $serverKey;
        MidtransConfig::$isProduction = $isProduction;
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;

        Log::debug('MidtransService: init', [
            'server_key_prefix' => substr($serverKey ?? '', 0, 15) . '…',
            'is_production'     => $isProduction,
        ]);
    }

    /**
     * Create a new Snap payment transaction.
     * Returns the Order model with snap_token and redirect_url filled.
     */
    public function createTransaction(Authenticatable $user, string $packageId): Order
    {
        // Fetch package
        $package = PaketBeasiswa::findOrFail($packageId);
        $amount  = (int) $package->harga;

        // Build unique order ID
        $orderId = 'ORD-' . $user->getAuthIdentifier() . '-' . time();

        // User info
        $userName  = $user->name  ?? $user->nama_mentor  ?? 'User';
        $userEmail = $user->email ?? 'noreply@nextgen.id';

        // Persist order (status: pending)
        $order = Order::create([
            'order_id'     => $orderId,
            'user_id'      => (string) $user->getAuthIdentifier(),
            'user_name'    => $userName,
            'user_email'   => $userEmail,
            'package_id'   => $packageId,
            'package_name' => $package->nama_beasiswa ?? 'Paket Beasiswa',
            'amount'       => $amount,
            'status'       => 'pending',
        ]);

        // Midtrans Snap params
        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $userName,
                'email'      => $userEmail,
            ],
            'item_details' => [[
                'id'       => $packageId,
                'price'    => $amount,
                'quantity' => 1,
                'name'     => $package->nama_beasiswa ?? 'Paket Beasiswa',
            ]],
            'callbacks' => [
                'finish'  => url('/payment/finish'),
                'error'   => url('/payment/error'),
                'pending' => url('/payment/pending'),
            ],
        ];

        // ── Call Midtrans Snap API ──
        $useMock = env('MIDTRANS_USE_MOCK', false);

        if ($useMock) {
            // Dev mock — skip Midtrans API call
            $snapToken   = 'mock-snap-' . uniqid();
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v4/redirection/' . $snapToken;
            Log::warning('MidtransService: using MOCK snap token (MIDTRANS_USE_MOCK=true)');
        } else {
            $snapToken   = Snap::getSnapToken($params);
            $redirectUrl = "https://app.sandbox.midtrans.com/snap/v4/redirection/{$snapToken}";
        }

        $order->update([
            'snap_token'   => $snapToken,
            'redirect_url' => $redirectUrl,
        ]);

        Log::info('MidtransService: created order', [
            'order_id'   => $orderId,
            'amount'     => $amount,
            'snap_token' => substr($snapToken, 0, 20) . '…',
        ]);

        return $order->fresh();
    }

    /**
     * Actively check transaction status from Midtrans API.
     * Called when Flutter polls order status — fixes the localhost webhook problem
     * where Midtrans cannot reach 127.0.0.1 to deliver webhook notifications.
     */
    public function checkTransactionStatus(Order $order): Order
    {
        if ($order->status !== 'pending') {
            return $order; // already settled, skip API call
        }

        try {
            $result      = Transaction::status($order->order_id);
            $transStatus = $result->transaction_status ?? '';
            $fraudStatus = $result->fraud_status ?? 'accept';

            $newStatus = match (true) {
                $transStatus === 'capture'    && $fraudStatus === 'accept' => 'paid',
                $transStatus === 'settlement'                              => 'paid',
                in_array($transStatus, ['cancel', 'deny'])                 => 'failed',
                $transStatus === 'expire'                                  => 'expired',
                default                                                    => 'pending',
            };

            if ($newStatus !== 'pending') {
                $updateData = ['status' => $newStatus];
                if ($newStatus === 'paid') {
                    $updateData['paid_at'] = now();
                    $this->grantBeasiswaAccess($order);
                }
                $order->update($updateData);
                $order = $order->fresh();

                Log::info('MidtransService: status polled', [
                    'order_id'   => $order->order_id,
                    'new_status' => $newStatus,
                ]);
            }
        } catch (\Throwable $e) {
            // 404 means transaction not yet created in Midtrans (e.g. user hasn't paid)
            Log::debug('MidtransService: status check skipped', [
                'order_id' => $order->order_id,
                'reason'   => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Handle Midtrans webhook notification.
     * Returns the updated Order.
     */
    public function handleWebhook(array $payload): Order
    {
        $orderId = $payload['order_id'] ?? '';
        $order   = Order::where('order_id', $orderId)->firstOrFail();

        // Verify signature
        $serverKey = config('midtrans.server_key');
        $expected  = hash('sha512',
            $orderId .
            $payload['status_code'] .
            $payload['gross_amount'] .
            $serverKey
        );

        if ($expected !== ($payload['signature_key'] ?? '')) {
            Log::warning('MidtransService: signature mismatch', ['order_id' => $orderId]);
            throw new \Exception('Invalid Midtrans signature');
        }

        $transStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $newStatus = match (true) {
            $transStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            $transStatus === 'settlement'                           => 'paid',
            in_array($transStatus, ['cancel', 'deny'])              => 'failed',
            $transStatus === 'expire'                               => 'expired',
            default                                                 => $order->status,
        };

        $updateData = [
            'status'             => $newStatus,
            'midtrans_response'  => json_encode($payload),
        ];

        if ($newStatus === 'paid') {
            $updateData['paid_at'] = now();
            // Grant beasiswa access to student
            $this->grantBeasiswaAccess($order);
        }

        $order->update($updateData);

        Log::info('MidtransService: webhook processed', [
            'order_id'  => $orderId,
            'old_status'=> $order->getOriginal('status'),
            'new_status'=> $newStatus,
        ]);

        return $order->fresh();
    }

    /**
     * After successful payment, add beasiswa to user's beasiswa_diampu.
     */
    private function grantBeasiswaAccess(Order $order): void
    {
        $user = User::find($order->user_id);
        if (! $user) return;

        $package = PaketBeasiswa::find($order->package_id);
        if (! $package) return;

        $beasiswaName    = $package->nama_beasiswa;
        $currentBeasiswa = is_array($user->beasiswa_diampu)
            ? $user->beasiswa_diampu
            : (json_decode($user->beasiswa_diampu ?? '[]', true) ?? []);

        if (! in_array($beasiswaName, $currentBeasiswa)) {
            $currentBeasiswa[] = $beasiswaName;
            $user->update(['beasiswa_diampu' => array_values($currentBeasiswa)]);

            Log::info('MidtransService: granted beasiswa access', [
                'user_id'  => $order->user_id,
                'beasiswa' => $beasiswaName,
            ]);
        }
    }
}
