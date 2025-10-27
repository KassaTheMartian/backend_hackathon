<?php

namespace App\Data\User;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[Min(8)]
        public string $password,
    ) {
    }
}


