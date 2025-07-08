<?php

namespace App\Jobs;

use App\Mail\CouponNotification;
use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCouponEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Coupon $coupon,
        public string $message,
        public string $recipientEmail
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(
            new CouponNotification($this->coupon, $this->message, $this->recipientEmail)
        );
    }
}
