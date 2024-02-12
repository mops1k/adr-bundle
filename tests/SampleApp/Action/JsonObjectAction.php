<?php

namespace AdrBundle\Test\SampleApp\Action;

use AdrBundle\Attribute\Responder;
use AdrBundle\Response\Responder\JsonResponder;
use AdrBundle\Test\SampleApp\DTO\TestModel;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/json-object')]
#[Responder(JsonResponder::class)]
class JsonObjectAction
{
    public function __invoke(): TestModel
    {
        return new TestModel();
    }
}
