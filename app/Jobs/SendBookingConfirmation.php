<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // In a real implementation, you would send an email here
            // For now, we'll just log the action
            
            Log::info('Sending booking confirmation', [
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'email' => $this->booking->user_id ? 
                    $this->booking->user->email : 
                    $this->booking->guest_email,
            ]);

            // Update booking status
            $this->booking->update([
                'confirmation_sent' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendBookingConfirmation job failed', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage(),
        ]);
    }
}