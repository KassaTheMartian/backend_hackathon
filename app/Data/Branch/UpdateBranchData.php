<?php

namespace App\Data\Branch;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;

class UpdateBranchData extends Data
{
    public function __construct(
        #[Max(255)]
        public ?string $name,
        
        public ?string $description,
        
        #[Max(500)]
        public ?string $address,
        
        #[Max(20)]
        public ?string $phone,
        
        #[Max(100)]
        public ?string $email,
        
        public ?string $image,
        
        public ?float $latitude,
        
        public ?float $longitude,
        
        #[Max(255)]
        public ?string $opening_hours,
        
        #[BooleanType]
        public ?bool $is_active,
    ) {
    }
}
