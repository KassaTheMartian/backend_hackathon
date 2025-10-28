<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
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
            // Load relationships needed for email
            $this->booking->load(['user', 'service', 'branch', 'staff']);
            
            // Get recipient email
            $recipientEmail = $this->booking->user_id ? 
                $this->booking->user->email : 
                $this->booking->guest_email;

            // Send beautiful booking confirmation email
            Mail::to($recipientEmail)->send(new BookingConfirmationMail($this->booking));
            
            Log::info('Booking confirmation email sent successfully', [
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'email' => $recipientEmail,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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