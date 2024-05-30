<?php

namespace App\Core\UI\Controller;

use App\Core\UI\Api\Controller\AbstractRestController;
use Symfony\Component\HttpFoundation\Response;

final class DefaultController extends AbstractRestController
{
    public function index(): Response
    {
        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
