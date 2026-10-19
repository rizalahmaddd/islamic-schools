<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OnlinePaymentTransaction;
use App\Services\TripayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Menangani callback webhook pembayaran dari TriPay secara aman bebas double-credit.
     */
    public function __invoke(Request $request, TripayService $tripayService): JsonResponse
    {
        $signature = $request->header('X-Callback-Signature');
        $rawContent = (string) $request->getContent();

        if (! $tripayService->verifyCallbackSignature($rawContent, $signature)) {
            Log::warning('TriPay Webhook signature mismatch', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid callback signature.',
            ], 403);
        }

        $merchantRef = (string) ($request->input('merchant_ref') ?: $request->input('order_id'));
        $status = strtoupper((string) $request->input('status'));

        if (blank($merchantRef)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter merchant_ref tidak ditemukan.',
            ], 422);
        }

        $transaction = OnlinePaymentTransaction::where('order_id', $merchantRef)->first();

        if (! $transaction) {
            Log::warning('TriPay Webhook transaction not found: '.$merchantRef);

            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan di sistem.',
            ], 404);
        }

        if ($status === 'PAID') {
            $result = $tripayService->processSuccessfulPayment($transaction, $request->all());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'receipt_number' => $result['receipt_number'] ?? null,
            ]);
        }

        if ($status === 'EXPIRED') {
            $transaction->update(['status' => 'expired']);

            return response()->json([
                'success' => true,
                'message' => 'Status transaksi diperbarui menjadi kedaluwarsa.',
            ]);
        }

        if ($status === 'FAILED') {
            $transaction->update(['status' => 'failed']);

            return response()->json([
                'success' => true,
                'message' => 'Status transaksi diperbarui menjadi gagal.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event diterima namun tidak ada pembaruan status.',
        ]);
    }
}
