<?php

namespace App\Module\ML\Application\Interaction\Query\AskForPrediction;

use App\Core\Infrastructure\Interaction\Query\QueryInterface;

readonly class AskForPredictionQuery implements QueryInterface
{
    public function __construct(
        public string $message,
    ) {
    }
}
