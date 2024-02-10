<?php

namespace AdrBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Responder
{
    public function __construct(
        public string $class,
        /**
         * @var array<string, mixed>
         */
        public array $responseArguments = []
    ) {
    }
}
