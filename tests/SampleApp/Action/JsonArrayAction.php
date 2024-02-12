<?php

namespace AdrBundle\Test\SampleApp\Action;

use AdrBundle\Attribute\Responder;
use AdrBundle\Response\Responder\JsonResponder;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/json-array')]
#[Responder(JsonResponder::class)]
class JsonArrayAction
{
    /**
     * @return array{success: bool}
     */
    public function __invoke(): array
    {
        return [
            'success' => true,
        ];
    }
}
