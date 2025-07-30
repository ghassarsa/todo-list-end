<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index() {
        $user = Auth::user();
        $payments = Payment::whereHas('order.user', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->get();

        return response()->json($payments);
    }

    public function store(Request $request) {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$clientKey = env('MIDTRANS_CLIENT_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        \Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS', true);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::findOrFail($request->order_id);
            if ($validator->fails()) {
                return response()->json(['error' => 'Order is not in pending status'], 400);
            }
            
            $payments = Payment::where('order_id', $order->id)->first();
            if ($payments) {
                return response()->json(['error' => 'Payment already exists for this order'], 400);
            }

            $newPayment = Payment::create([
                'order_id' => $order->id,
                'paid_at' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
            ]);

            $midtransPayLoad = [
                'transaction_details' => [
                    'order_id' => $order->id . '-' . uniqid(),
                    'gross_amount' => round($order->amount),
                ],
                'customer_details' => [
                    'email' => $user->email,
                    'first_name' => $user->name,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($midtransPayLoad);

            $newPayment->update([
                'snap_token' => $snapToken,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment created',
                'payment' => $newPayment,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Transaction Failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id) {
        $user = Auth::user();
        $payment = Payment::findOrFail($id);
        if ($payment->order->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($payment);
    }

    public function pdf(Invoice $invoice) {
        return view('pdf.invoice', compact('invoice'));
    }

    public function generateInvoice($orderId, $user, $plan) {
        $invoiceNumber = 'INV-' . strtoupper(uniqid());
        $data = [
            'invoice_number' => $invoiceNumber,
            'date' => now()->format('d M Y'),
            'transaction_status' => 'success',
            'user_name' => $user->name,
            'user_email' => $user->email,
            'order_id' => $orderId,
            'plan_name' => $plan->name,
            'price' => $plan->price,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        Storage::disk('public')->put("invoices/{$user->email}/{$invoiceNumber}.pdf", $pdf->output());
        $invoice = Invoice::create([
            'order_id' => $orderId,
            'invoice_number' => $invoiceNumber,
            'pdf_url' => asset("storage/invoices/{$user->email}/{$invoiceNumber}.pdf"),
        ]);
        // dd($invoice);
        return $invoice;
    }

    public function callback(Request $request) {
        $orderId = explode('-', $request->order_id)[0];
        $order = Order::find($orderId);

        $user = $order->user;
        $plan = $order->plan;
        // dd($request);
if ($request->transaction_status === 'settlement') {
    $payment = Payment::where('order_id', $orderId)->first();
    if (!$payment) {
        return response()->json(['error' => 'Payment not found.'], 404);
    }

    $payment->update([
        'transaction_status' => 'success',
        'paid_at' => now(),
    ]);
    $order->update(['status' => 'completed']);

    $user->update([
        'plan_id' => $plan->id,
        'status' => 'premium',
    ]);

    $this->generateInvoice($orderId, $user, $plan);

    return response()->json(['message' => 'Payment successful', 'payment' => $payment], 200);
}

    }
}
