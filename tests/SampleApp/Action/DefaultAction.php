<?php

namespace AdrBundle\Test\SampleApp\Action;

use AdrBundle\Attribute\Responder;
use AdrBundle\Response\Responder\DefaultResponder;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/default')]
#[Responder(DefaultResponder::class)]
class DefaultAction
{
    public function __invoke(): ?array
    {
        return null;
    }
}
