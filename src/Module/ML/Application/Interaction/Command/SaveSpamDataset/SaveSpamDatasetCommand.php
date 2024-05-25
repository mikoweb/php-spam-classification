<?php

namespace App\Module\ML\Application\Interaction\Command\SaveSpamDataset;

use App\Core\Infrastructure\Interaction\Command\CommandInterface;

readonly class SaveSpamDatasetCommand implements CommandInterface
{
    public function __construct(
        public string $outputDatasetFilename,

        /**
         * @var array<string[]>
         */
        public array $samples,

        /**
         * @var string[]
         */
        public array $labels,
    ) {
    }
}
