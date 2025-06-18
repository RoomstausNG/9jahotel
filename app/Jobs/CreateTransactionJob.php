<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $bookingInvoice) {}

    public function handle()
    {
        // Ensure hotel_payable is calculated and saved
        if (method_exists($this->bookingInvoice, 'calculateAndSaveCommission')) {
            $this->bookingInvoice->calculateAndSaveCommission();
            $this->bookingInvoice->refresh(); // Get latest values
        }
        $reference = date('Y') . time();
        Transaction::create([
            'booking_invoice_id' => $this->bookingInvoice->id,
            'amount' => $this->bookingInvoice->hotel_payable,
            'payment_method' => $this->bookingInvoice->payment_method ?? 'wallet',
            'reference' => $reference,
            'hotel_id' => $this->bookingInvoice->hotel_id,
            'status' => 'pending',
        ]);
    }
}
