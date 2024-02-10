<?php

namespace AdrBundle\Response\Contract;

use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    /**
     * @param array<string, object> $attributes
     * @param array<string, mixed>  $responseArguments
     */
    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response;
}
