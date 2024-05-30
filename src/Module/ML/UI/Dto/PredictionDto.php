<?php

namespace App\Module\ML\UI\Dto;

readonly class PredictionDto
{
    public function __construct(
        public bool $isSpam,
    ) {
    }
}
