<?php

namespace AdrBundle\Test\SampleApp\Action;

use AdrBundle\Attribute\Responder;
use AdrBundle\Response\Responder\FileResponder;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file')]
#[Responder(FileResponder::class)]
class FileAction
{
    public function __invoke(): string
    {
        return __DIR__.'/../Resources/sample.txt';
    }
}
