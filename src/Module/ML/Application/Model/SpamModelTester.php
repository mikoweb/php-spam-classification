<?php

namespace App\Module\ML\Application\Model;

use App\Core\Application\Path\AppPathResolver;
use App\Module\ML\Application\Model\VO\TestInput;
use App\Module\ML\Application\Model\VO\TestResult;
use App\Module\ML\Application\Utils\WordsUtils;
use App\Module\ML\Domain\Constant;
use Ramsey\Collection\Collection;
use Ramsey\Collection\CollectionInterface;
use Ramsey\Collection\Map\TypedMap;
use Ramsey\Collection\Sort;
use Rubix\ML\CrossValidation\KFold;
use Rubix\ML\CrossValidation\Metrics\FBeta;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @see https://docs.rubixml.com/latest/cross-validation.html#validators
 */
class SpamModelTester
{
    private static ?SymfonyStyle $io = null;

    public function __construct(
        private readonly AppPathResolver $appPathResolver,
    ) {
    }

    /**
     * @return CollectionInterface<TestResult>
     */
    public function test(
        string $testingDatasetFilename,
        int $foldsNumber = Constant::DEFAULT_FOLDS_NUMBER
    ): CollectionInterface {
        $parametersCollection = $this->generateParameters();
        $results = new Collection(TestResult::class);

        $dataset = Labeled::fromIterator(new CSV(
            $this->appPathResolver->getDatasetPath($testingDatasetFilename),
            header: true,
        ));

        $uniqueWordsNum = WordsUtils::countUniqueWords($dataset->samples(), Constant::DEFAULT_MIN_WORDS_COUNT);
        $progressBar = $this->startProgressBar($parametersCollection->count());

        foreach ($parametersCollection as $testParams) {
            /** @var TestInput $testParams */
            $estimator = LearnerFactory::createLearner(
                uniqueWordsNum: $uniqueWordsNum,
                minDocumentCount: $testParams->minDocumentCount,
                maxDocumentRatio: $testParams->maxDocumentRatio,
                treeEstimators: $testParams->treeEstimators,
                treeRatio: $testParams->treeRatio,
            );

            $validator = new KFold($foldsNumber);
            $score = $validator->test($estimator, $dataset, new FBeta());

            $results->add(new TestResult(
                score: $score,
                parameters: $testParams,
            ));

            $progressBar?->advance();
        }

        $progressBar?->finish();

        return $results->sort('score', Sort::Descending);
    }

    public static function setIo(?SymfonyStyle $io): void
    {
        self::$io = $io;
    }

    /**
     * @return CollectionInterface<TestInput>
     */
    private function generateParameters(): CollectionInterface
    {
        $collection = new Collection(TestInput::class);
        $range = $this->createRange();

        foreach ($range->get('minDocumentCount') as $minDocumentCount) {
            foreach ($range->get('maxDocumentRatio') as $maxDocumentRatio) {
                foreach ($range->get('treeEstimators') as $treeEstimators) {
                    foreach ($range->get('treeRatio') as $treeRatio) {
                        $collection->add(new TestInput(
                            minDocumentCount: $minDocumentCount,
                            maxDocumentRatio: $maxDocumentRatio,
                            treeEstimators: $treeEstimators,
                            treeRatio: $treeRatio,
                        ));
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * @return TypedMap<string, iterable<int|float>>
     */
    private function createRange(): TypedMap
    {
        /* @phpstan-ignore-next-line */
        return new TypedMap('string', 'array', [
            'minDocumentCount' => range(2, 4),
            'maxDocumentRatio' => range(0.3, 0.6, 0.1),
            'treeEstimators' => range(100, 200, 100),
            'treeRatio' => range(0.1, 0.3, 0.1),
        ]);
    }

    private function startProgressBar(int $max): ?ProgressBar
    {
        ProgressBar::setFormatDefinition(
            'spam_model_tester',
            '%current%/%max% of Property sets [%bar%] %percent:3s%%'
        );

        $progressBar = self::$io?->createProgressBar($max);
        $progressBar?->setFormat('spam_model_tester');
        $progressBar?->start();

        return $progressBar;
    }
}
