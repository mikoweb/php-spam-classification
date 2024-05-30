<?php

namespace App\Module\ML\UI\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class PredictRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $message,
    ) {
    }
}
