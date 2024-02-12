<?php

namespace AdrBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

final class PostRespondEvent extends Event
{
    public const NAME = 'adr.post_respond';

    public function __construct(
        public readonly Response $response
    ) {
    }
}
