<?php

namespace App\Data\Service;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;

class ServiceData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,
        
        public ?string $description,
        
        #[Required]
        #[Numeric]
        public float $price,
        
        #[Required]
        #[IntegerType]
        public int $duration,
        
        #[Required]
        #[IntegerType]
        public int $category_id,
        
        #[BooleanType]
        public ?bool $is_active,
        
        public ?string $image,
        
        public ?array $features,
    ) {
    }
}
