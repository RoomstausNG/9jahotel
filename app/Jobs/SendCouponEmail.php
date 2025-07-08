<?php

namespace App\Jobs;

use App\Mail\CouponNotification;
use App\Models\Coupon;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCouponEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Coupon $coupon,
        public string $message,
        public string $recipientEmail,
        public ?int $adminUserId = null
    ) {
        $this->adminUserId = $adminUserId ?: auth()->id();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send(
                new CouponNotification($this->coupon, $this->message, $this->recipientEmail)
            );

            Log::info("Coupon email sent successfully", [
                'coupon_code' => $this->coupon->code,
                'recipient' => $this->recipientEmail,
            ]);

            // Send success notification to admin
            if ($this->adminUserId) {
                $admin = User::find($this->adminUserId);
                if ($admin) {
                    Notification::make()
                        ->title('Coupon Email Sent Successfully')
                        ->body("Coupon {$this->coupon->code} was successfully sent to {$this->recipientEmail}")
                        ->success()
                        ->sendToDatabase($admin);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send coupon email", [
                'coupon_code' => $this->coupon->code,
                'recipient' => $this->recipientEmail,
                'error' => $e->getMessage(),
            ]);

            // Send failure notification to admin
            if ($this->adminUserId) {
                $admin = User::find($this->adminUserId);
                if ($admin) {
                    Notification::make()
                        ->title('Coupon Email Failed')
                        ->body("Failed to send coupon {$this->coupon->code} to {$this->recipientEmail}. Error: {$e->getMessage()}")
                        ->danger()
                        ->sendToDatabase($admin);
                }
            }

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        if ($this->adminUserId) {
            $admin = User::find($this->adminUserId);
            if ($admin) {
                Notification::make()
                    ->title('Coupon Email Failed Permanently')
                    ->body("Permanently failed to send coupon {$this->coupon->code} to {$this->recipientEmail} after {$this->tries} attempts.")
                    ->danger()
                    ->sendToDatabase($admin);
            }
        }
    }
}
