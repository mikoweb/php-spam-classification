<?php

namespace App\Module\ML\Application\Interaction\Command\Handler;

use App\Core\Application\Path\AppPathResolver;
use App\Module\ML\Application\Interaction\Command\GenerateSpamCleansedDatasetCommand;
use App\Module\ML\Infrastructure\Reader\SpamDatasetReader;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use UnexpectedValueException;

readonly class GenerateSpamCleansedDatasetHandler
{
    public function __construct(
        private SpamDatasetReader $spamDatasetReader,
        private AppPathResolver $appPathResolver,
    ) {
    }

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    #[AsMessageHandler(bus: 'command_bus')]
    public function handle(GenerateSpamCleansedDatasetCommand $command): void
    {
        $inputDataset = $this->spamDatasetReader->read($command->inputFilename);

        if ($inputDataset->isEmpty()) {
            throw new UnexpectedValueException('Input dataset is empty!');
        }

        $header = array_keys($inputDataset[0]);

        $writer = Writer::createFromPath(
            $this->appPathResolver->getDatasetPath($command->outputCleansedFilename),
            'w+'
        );

        $writer->insertOne($header);
        $writer->insertAll($inputDataset->toArray());
    }
}
