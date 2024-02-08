<?php

namespace AdrBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class SerializerContext
{
    public function __construct(
        public array $context
    ) {
    }
}
