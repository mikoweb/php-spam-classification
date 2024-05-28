<?php

namespace App\Module\ML\Application\Model;

use App\Core\Application\Path\AppPathResolver;
use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\ML\Application\Interaction\Command\SaveSpamDataset\SaveSpamDatasetCommand;
use App\Module\ML\Application\Utils\WordsUtils;
use App\Module\ML\Domain\Constant;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;

class SpamModelTrainer
{
    private static ?SymfonyStyle $io = null;

    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly AppPathResolver $appPathResolver,
    ) {
    }

    public function train(
        string $trainingDatasetFilename,
        string $outputModelFilename,
        float $splitRatio = Constant::DEFAULT_SPLIT_RATIO,
        int $minWordsCount = Constant::DEFAULT_MIN_WORDS_COUNT,
        int $minDocumentCount = Constant::DEFAULT_MIN_DOCUMENT_COUNT,
        float $maxDocumentRatio = Constant::DEFAULT_MAX_DOCUMENT_RATIO,
        string $language = Constant::DEFAULT_LANGUAGE,
        int $treeMaxHeight = PHP_INT_MAX,
        int $treeEstimators = Constant::DEFAULT_TREE_ESTIMATORS,
        float $treeRatio = Constant::DEFAULT_TREE_RATIO,
        bool $treeBalanced = Constant::DEFAULT_TREE_BALANCED,
        bool $history = true,
    ): void {
        $dataset = Labeled::fromIterator(new CSV(
            $this->appPathResolver->getDatasetPath($trainingDatasetFilename),
            header: true,
        ));

        self::$io?->info('Dataset was loaded.');

        [$training, $testing] = $dataset->stratifiedSplit($splitRatio);

        self::$io?->info('Dataset was splitted into training and testing.');

        $this->saveDataset(Constant::DEFAULT_SPAM_TRAINING_DATASET_FILENAME, $training->samples(), $training->labels());
        $this->saveDataset(Constant::DEFAULT_SPAM_TESTING_DATASET_FILENAME, $testing->samples(), $testing->labels());

        self::$io?->info(sprintf('The training dataset contains `%d` samples.', $training->numSamples()));

        $modelPath = $this->appPathResolver->getModelPath($outputModelFilename);
        $uniqueWordsNum = WordsUtils::countUniqueWords($dataset->samples(), $minWordsCount);

        $estimator = new PersistentModel(
            LearnerFactory::createLearner(
                uniqueWordsNum: $uniqueWordsNum,
                minDocumentCount: $minDocumentCount,
                maxDocumentRatio: $maxDocumentRatio,
                language: $language,
                treeMaxHeight: $treeMaxHeight,
                treeEstimators: $treeEstimators,
                treeRatio: $treeRatio,
                treeBalanced: $treeBalanced,
            ),
            new Filesystem($modelPath, $history)
        );

        self::$io?->info('The training process has begun!');
        $estimator->train($training);

        self::$io?->info('Saving the model...');
        $estimator->save();

        self::$io?->info(sprintf('The model has been created in a file `%s`.', $modelPath));
    }

    public static function setIo(?SymfonyStyle $io): void
    {
        self::$io = $io;
    }

    /**
     * @param array<string[]> $samples
     * @param string[]        $labels
     */
    private function saveDataset(
        string $outputDatasetFilename,
        array $samples,
        array $labels
    ): void {
        $this->commandBus->dispatch(new SaveSpamDatasetCommand(
            $outputDatasetFilename,
            $samples,
            $labels,
        ));

        self::$io?->info(sprintf('Saved dataset `%s`.', $outputDatasetFilename));
    }
}
