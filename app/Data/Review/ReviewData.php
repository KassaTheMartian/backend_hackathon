<?php

namespace App\Data\Review;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class ReviewData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        public int $user_id,
        
        #[Required]
        #[IntegerType]
        public int $booking_id,
        
        #[Required]
        #[IntegerType]
        public int $service_id,
        
        public ?int $staff_id,
        
        public ?int $branch_id,
        
        #[Required]
        #[IntegerType, Min(1), Max(5)]
        public int $rating,
        
        #[Required]
        #[Max(1000)]
        public string $comment,
        
        public ?array $images,
    ) {
    }
}
