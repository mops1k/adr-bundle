<?php

namespace AdrBundle\Response\Contract;

use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response;
}
