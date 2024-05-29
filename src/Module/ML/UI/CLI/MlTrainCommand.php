<?php

namespace App\Module\ML\UI\CLI;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\ML\Application\Interaction\Command\GenerateSpamCleansedDataset\GenerateSpamCleansedDatasetCommand;
use App\Module\ML\Application\Model\SpamModelTrainer;
use App\Module\ML\Domain\Constant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:ml:train',
    description: 'Command to training a spam classification model.',
)]
class MlTrainCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly SpamModelTrainer $spamModelTrainer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');
        $io = new SymfonyStyle($input, $output);

        $stopwatch = new Stopwatch();
        $stopwatch->start('train');

        $io->info('Generating cleansed dataset...');

        $this->commandBus->dispatch(new GenerateSpamCleansedDatasetCommand(
            Constant::DEFAULT_SPAM_DATASET_FILENAME,
            Constant::DEFAULT_SPAM_CLEANSED_DATASET_FILENAME,
        ));

        SpamModelTrainer::setIo($io);

        $this->spamModelTrainer->train(
            Constant::DEFAULT_SPAM_CLEANSED_DATASET_FILENAME,
            Constant::SPAM_MODEL_FILENAME,
        );

        $profile = $stopwatch->stop('train');

        $io->section(u('Profile data:')->padEnd(40, ' ')->toString());
        $io->table([
            'Parameter',
            'Value',
        ], [
            ['End date', date('Y-m-d H:i:s')],
            ['Execution time', round($profile->getDuration() / 1000 / 60, 2) . ' min'],
            ['Memory', round($profile->getMemory() / 1024 / 1024, 2) . ' MiB'],
        ]);

        $io->success('Training complete!');

        return Command::SUCCESS;
    }
}
