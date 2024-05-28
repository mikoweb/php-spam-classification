<?php

namespace App\Module\ML\UI\CLI;

use App\Module\ML\Application\Model\SpamModelReport;
use App\Module\ML\Domain\Constant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ml:report',
    description: 'Command to generate a report about the model.',
)]
class MlReportCommand extends Command
{
    public function __construct(
        private readonly SpamModelReport $spamModelReport,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Generating report...');

        $io->writeln($this->spamModelReport->generateReport(
            Constant::DEFAULT_SPAM_TESTING_DATASET_FILENAME,
            Constant::SPAM_MODEL_FILENAME,
        )->toJSON());

        $io->success('The report has been generated!');

        return Command::SUCCESS;
    }
}
