<?php

namespace App\Data\Demo;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class DemoData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $title,
        public ?string $description,
        #[BooleanType]
        public ?bool $is_active,
    ) {
    }
}


