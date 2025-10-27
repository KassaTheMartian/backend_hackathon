<?php

namespace App\Data\Service;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;

class UpdateServiceData extends Data
{
    public function __construct(
        #[Max(255)]
        public ?string $name,
        
        public ?string $description,
        
        #[Numeric]
        public ?float $price,
        
        #[IntegerType]
        public ?int $duration,
        
        #[IntegerType]
        public ?int $category_id,
        
        #[BooleanType]
        public ?bool $is_active,
        
        public ?string $image,
        
        public ?array $features,
    ) {
    }
}
