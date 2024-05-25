<?php

namespace App\Module\ML\Application\Interaction\Command\SaveSpamDataset\Handler;

use App\Core\Application\Path\AppPathResolver;
use App\Module\ML\Application\Interaction\Command\SaveSpamDataset\SaveSpamDatasetCommand;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

readonly class SaveSpamDatasetHandler
{
    public function __construct(
        private AppPathResolver $appPathResolver,
    ) {
    }

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    #[AsMessageHandler(bus: 'command_bus')]
    public function handle(SaveSpamDatasetCommand $command): void
    {
        $dataset = [];

        foreach ($command->samples as $k => $sample) {
            $dataset[] = [...$sample, $command->labels[$k]];
        }

        if (file_exists($command->outputDatasetFilename)) {
            unlink($command->outputDatasetFilename);
        }

        $writer = Writer::createFromPath(
            $this->appPathResolver->getDatasetPath($command->outputDatasetFilename),
            'w+'
        );

        $writer->insertOne(['message', 'is_spam']);
        $writer->insertAll($dataset);
    }
}
