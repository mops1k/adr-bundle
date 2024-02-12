<?php

namespace AdrBundle\Response;

use AdrBundle\Attribute\Responder;
use AdrBundle\Event\PostRespondEvent;
use AdrBundle\Response\Contract\ResponderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

final class ResponseResolver
{
    public function __construct(
        /**
         * @var array<mixed>|\Traversable<mixed>
         */
        private iterable $responders,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $withKeysMap = [];
        foreach ($this->responders as $responder) {
            if (!$responder instanceof ResponderInterface) {
                throw new \RuntimeException(\sprintf(
                    'Responder "%s" must implement "%s".',
                    $responder::class,
                    ResponderInterface::class
                ));
            }
            $withKeysMap[$responder::class] = $responder;
        }

        $this->responders = $withKeysMap;
    }

    /**
     * @param array<string, mixed> $actionArguments
     */
    public function resolve(callable|object $action, array $actionArguments): Response
    {
        if (!\is_object($action)) {
            throw new \RuntimeException('Action must be a callable object with "__invoke" method.');
        }

        if (!\is_callable($action)) {
            throw new \RuntimeException(\sprintf(
                'Action "%s" must be a callable.',
                $action::class
            ));
        }

        $attributes = $this->resolveAttributes($action);
        if (!\array_key_exists(Responder::class, $attributes)) {
            throw new \RuntimeException(\sprintf(
                'No responder configured for action "%s".',
                $action::class
            ));
        }

        /** @var Responder $responderAttribute */
        $responderAttribute = $attributes[Responder::class];
        unset($attributes[Responder::class]);

        /* @phpstan-ignore-next-line */
        if (!\array_key_exists($responderAttribute->class, $this->responders)) {
            throw new \RuntimeException(\sprintf(
                'No responder "%s" found for action "%s".',
                $responderAttribute->class,
                $action::class
            ));
        }

        /** @var ResponderInterface $responder */
        $responder = $this->responders[$responderAttribute->class];

        $response = $responder($action(...$actionArguments), $attributes, $responderAttribute->responseArguments);

        $event = new PostRespondEvent($response);
        /* @phpstan-ignore-next-line */
        $this->dispatcher->dispatch($event, $event::NAME);

        return $response;
    }

    /**
     * @return array<string, object>
     */
    private function resolveAttributes(object $action): array
    {
        $reflectionClass = new \ReflectionClass($action);
        $reflectionAttributes = $reflectionClass->getAttributes();
        $attributes = [];
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $attributes[$reflectionAttribute->getName()] = $reflectionAttribute->newInstance();
        }

        return $attributes;
    }
}
