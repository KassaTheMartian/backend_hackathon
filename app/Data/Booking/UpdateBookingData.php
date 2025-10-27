<?php

namespace App\Data\Booking;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class UpdateBookingData extends Data
{
    public function __construct(
        #[IntegerType]
        public ?int $branch_id,
        
        #[IntegerType]
        public ?int $service_id,
        
        #[IntegerType]
        public ?int $staff_id,
        
        #[Date]
        #[DateFormat('Y-m-d')]
        public ?string $booking_date,
        
        #[DateFormat('H:i')]
        public ?string $booking_time,
        
        #[Max(1000)]
        public ?string $notes,
        
        public ?string $promotion_code,
    ) {
    }
}
