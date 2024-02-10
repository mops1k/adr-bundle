<?php

namespace AdrBundle\Response\Responder;

use AdrBundle\Response\Contract\ResponderInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultResponder implements ResponderInterface
{
    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response
    {
        if (null !== $data && !is_string($data)) {
            throw new \RuntimeException(\sprintf('Action must return string or null, %s given.', gettype($data)));
        }

        return new Response($data, ...$responseArguments);
    }
}
