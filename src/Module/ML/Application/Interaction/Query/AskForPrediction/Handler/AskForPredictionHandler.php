<?php

namespace App\Module\ML\Application\Interaction\Query\AskForPrediction\Handler;

use App\Core\Application\Path\AppPathResolver;
use App\Module\ML\Application\Interaction\Query\AskForPrediction\AskForPredictionQuery;
use App\Module\ML\Domain\Constant;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function Symfony\Component\String\u;

readonly class AskForPredictionHandler
{
    public function __construct(
        private AppPathResolver $appPathResolver,
    ) {
    }

    #[AsMessageHandler(bus: 'query_bus')]
    public function handle(AskForPredictionQuery $query): bool
    {
        $estimator = PersistentModel::load(new Filesystem(
            $this->appPathResolver->getModelPath(Constant::SPAM_MODEL_FILENAME)
        ));

        $predictions = $estimator->predict(new Unlabeled([
            [
                trim(strip_tags(html_entity_decode(
                    u($query->message)
                        ->replaceMatches('/\r\n|\r|\n/is', ' ')
                        ->replaceMatches('/\s+/uis', ' ')
                        ->collapseWhitespace()
                        ->toString(),
                ))),
            ],
        ]));

        return $predictions[0] === 'yes';
    }
}
