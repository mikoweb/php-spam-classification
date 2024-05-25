<?php

namespace App\Module\ML\Application\Interaction\Command\GenerateSpamCleansedDataset;

use App\Core\Infrastructure\Interaction\Command\CommandInterface;

readonly class GenerateSpamCleansedDatasetCommand implements CommandInterface
{
    public function __construct(
        public string $inputFilename,
        public string $outputCleansedFilename,
    ) {
    }
}
