<?php

namespace App\Module\ML\Application\Model;

use App\Core\Application\Path\AppPathResolver;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Report;

/**
 * @see https://docs.rubixml.com/latest/cross-validation.html
 * @see https://docs.rubixml.com/latest/cross-validation/reports/multiclass-breakdown.html
 * @see https://docs.rubixml.com/latest/cross-validation/reports/confusion-matrix.html
 */
readonly class SpamModelReport
{
    public function __construct(
        private AppPathResolver $appPathResolver,
    ) {
    }

    public function generateReport(
        string $testingDatasetFilename,
        string $modelFilename,
    ): Report {
        $dataset = Labeled::fromIterator(new CSV(
            $this->appPathResolver->getDatasetPath($testingDatasetFilename),
            header: true,
        ));

        $estimator = PersistentModel::load(new Filesystem(
            $this->appPathResolver->getModelPath($modelFilename)
        ));

        $predictions = $estimator->predict($dataset);

        $report = new AggregateReport([
            new MulticlassBreakdown(),
            new ConfusionMatrix(),
        ]);

        return $report->generate($predictions, $dataset->labels());
    }
}
