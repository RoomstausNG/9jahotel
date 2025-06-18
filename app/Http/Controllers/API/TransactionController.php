<?php

namespace App\Http\Controllers\API;

use App\Models\BookingInvoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreateTransactionJob;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    // POST: /api/transactions
    public function store(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|exists:booking_invoices,reference',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        $bookingInvoice = BookingInvoice::where('reference', $request->booking_reference)->firstOrFail();

        // Queue the transaction creation
        Bus::dispatch(new CreateTransactionJob($bookingInvoice));

        return response()->json(['message' => 'Transaction creation queued.'], 202);
    }

    // PATCH: /api/transactions/{reference}/status
    public function updateStatus(Request $request, $reference)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $transaction = Transaction::where('reference', $reference)->firstOrFail();
        $transaction->status = $request->status;
        $transaction->save();

        // Update BookingInvoice status as well
        $invoice = $transaction->bookingInvoice;
        if ($invoice) {
            $invoice->status = $request->status;
            $invoice->save();
        }

        return response()->json(['message' => 'Status updated.']);
    }
}