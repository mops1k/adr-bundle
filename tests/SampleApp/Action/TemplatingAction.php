<?php

namespace AdrBundle\Test\SampleApp\Action;

use AdrBundle\Attribute\Responder;
use AdrBundle\Response\Responder\TemplatingResponder;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/templating')]
#[Responder(TemplatingResponder::class)]
#[Template('sample.html.twig')]
class TemplatingAction
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [];
    }
}
