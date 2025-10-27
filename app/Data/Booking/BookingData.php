<?php

namespace App\Data\Booking;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class BookingData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        public int $branch_id,
        
        #[Required]
        #[IntegerType]
        public int $service_id,
        
        #[Required]
        #[IntegerType]
        public int $staff_id,
        
        #[Required]
        #[Date]
        #[DateFormat('Y-m-d')]
        public string $booking_date,
        
        #[Required]
        #[DateFormat('H:i')]
        public string $booking_time,
        
        #[Max(1000)]
        public ?string $notes,
        
        public ?string $promotion_code,
    ) {
    }
}
