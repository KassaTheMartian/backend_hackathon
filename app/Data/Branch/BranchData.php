<?php

namespace App\Data\Branch;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;

class BranchData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,
        
        public ?string $description,
        
        #[Required]
        #[Max(500)]
        public string $address,
        
        #[Required]
        #[Max(20)]
        public string $phone,
        
        #[Required]
        #[Max(100)]
        public string $email,
        
        public ?string $image,
        
        #[Required]
        public float $latitude,
        
        #[Required]
        public float $longitude,
        
        #[Required]
        #[Max(255)]
        public string $opening_hours,
        
        #[BooleanType]
        public ?bool $is_active,
    ) {
    }
}
