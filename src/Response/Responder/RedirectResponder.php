<?php

namespace AdrBundle\Response\Responder;

use AdrBundle\Response\Contract\ResponderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectResponder implements ResponderInterface
{
    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response
    {
        if (!is_string($data)) {
            throw new \LogicException('Action must return string.');
        }

        return new RedirectResponse($data, ...$responseArguments);
    }
}
