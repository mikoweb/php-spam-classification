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

#[AsCommand(
    name: 'app:ml:train',
    description: 'Command for training a spam classification model.',
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

        $io->success('Training complete!');

        return Command::SUCCESS;
    }
}
