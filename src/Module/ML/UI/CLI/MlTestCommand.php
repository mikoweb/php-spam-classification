<?php

namespace App\Module\ML\UI\CLI;

use App\Core\Application\Path\AppPathResolver;
use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\ML\Application\Interaction\Command\GenerateSpamCleansedDataset\GenerateSpamCleansedDatasetCommand;
use App\Module\ML\Application\Model\SpamModelTester;
use App\Module\ML\Domain\Constant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ml:test',
    description: 'Command to testing a spam classification model.',
)]
class MlTestCommand extends Command
{
    public function __construct(
        private readonly SpamModelTester $spamModelTester,
        private readonly CommandBusInterface $commandBus,
        private readonly AppPathResolver $appPathResolver,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Generating cleansed dataset...');

        $this->commandBus->dispatch(new GenerateSpamCleansedDatasetCommand(
            Constant::DEFAULT_SPAM_DATASET_FILENAME,
            Constant::DEFAULT_SPAM_CLEANSED_DATASET_FILENAME,
        ));

        $io->info('Testing model...');

        SpamModelTester::setIo($io);
        $results = $this->spamModelTester->test(Constant::DEFAULT_SPAM_CLEANSED_DATASET_FILENAME);

        $outputFile = $this->appPathResolver->getTestPath('results.json');
        file_put_contents($outputFile, json_encode($results->toArray(), JSON_PRETTY_PRINT));

        $io->writeln('');
        $io->writeln('');
        $io->success(sprintf('Generated results in a file `%s`', $outputFile));

        return Command::SUCCESS;
    }
}
