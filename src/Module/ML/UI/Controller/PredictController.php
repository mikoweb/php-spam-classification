<?php

namespace App\Module\ML\UI\Controller;

use App\Core\Infrastructure\Bus\QueryBusInterface;
use App\Core\UI\Api\Controller\AbstractRestController;
use App\Module\ML\Application\Interaction\Query\AskForPrediction\AskForPredictionQuery;
use App\Module\ML\UI\Dto\PredictionDto;
use App\Module\ML\UI\Dto\PredictRequestDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class PredictController extends AbstractRestController
{
    #[OA\Tag(name: 'ML')]
    #[OA\Post(requestBody: new OA\RequestBody(attachables: [new Model(type: PredictRequestDto::class)]))]
    #[OA\Response(
        response: 200,
        description: 'Prediction, whether the message is spam?',
        content: new OA\JsonContent(
            ref: new Model(type: PredictionDto::class),
            type: 'object',
        )
    )]
    public function predict(
        #[MapRequestPayload] PredictRequestDto $dto,
        QueryBusInterface $queryBus,
    ): Response {
        return $this->json(new PredictionDto(
            $queryBus->dispatch(new AskForPredictionQuery($dto->message))
        ));
    }
}
