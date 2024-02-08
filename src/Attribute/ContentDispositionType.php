<?php

namespace AdrBundle\Attribute;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ContentDispositionType
{
    public function __construct(public string $type = ResponseHeaderBag::DISPOSITION_ATTACHMENT)
    {
    }
}
