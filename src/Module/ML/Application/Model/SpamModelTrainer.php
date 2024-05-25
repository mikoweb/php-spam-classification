<?php

namespace App\Module\ML\Application\Model;

use App\Core\Application\Path\AppPathResolver;
use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\ML\Application\Interaction\Command\SaveSpamDataset\SaveSpamDatasetCommand;
use App\Module\ML\Domain\Constant;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Tokenizers\WordStemmer;
use Rubix\ML\Transformers\MultibyteTextNormalizer;
use Rubix\ML\Transformers\StopWordFilter;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\u;

/**
 * @see https://docs.rubixml.com/latest/classifiers/random-forest.html
 * @see https://docs.rubixml.com/latest/tokenizers/word-stemmer.html
 * @see https://docs.rubixml.com/latest/transformers/word-count-vectorizer.html
 * @see https://docs.rubixml.com/latest/transformers/stop-word-filter.html
 * @see https://docs.rubixml.com/latest/transformers/tf-idf-transformer.html
 * @see https://docs.rubixml.com/latest/transformers/z-scale-standardizer.html
 */
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
        float $splitRatio = 0.75,
        int $minDocumentCount = 2,
        float $maxDocumentRatio = 0.4,
        string $language = Constant::DEFAULT_LANGUAGE,
        int $treeMaxHeight = 10,
        int $treeEstimators = 100,
        float $treeRatio = 0.2,
        bool $treeBalanced = false,
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
        $estimator = new PersistentModel(
            new Pipeline([
                new MultibyteTextNormalizer(),
                new StopWordFilter(Constant::STOP_WORDS),
                new WordCountVectorizer(
                    maxVocabularySize: $this->countUniqueWords($dataset->samples(), 1),
                    minDocumentCount: $minDocumentCount,
                    maxDocumentRatio: $maxDocumentRatio,
                    tokenizer: new WordStemmer($language),
                ),
                new TfIdfTransformer(),
                new ZScaleStandardizer(),
            ], new RandomForest(
                new ClassificationTree($treeMaxHeight),
                estimators: $treeEstimators,
                ratio: $treeRatio,
                balanced: $treeBalanced,
            )),
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

    /**
     * @param array<string[]> $samples
     */
    private function countUniqueWords(array $samples, int $minCount): int
    {
        $words = [];

        foreach ($samples as $sample) {
            $items = array_filter(
                preg_split('/\s|<br\/>/', $sample[0]),
                fn (string $word) => !empty($word)
            );

            foreach ($items as $item) {
                $word = u($item)->snake()->toString();

                if (!isset($words[$word])) {
                    $words[$word] = 1;
                } else {
                    ++$words[$word];
                }
            }
        }

        return count(array_filter($words, fn (int $count) => $count >= $minCount));
    }
}
