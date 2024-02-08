<?php

namespace AdrBundle\Response\Responder;

use AdrBundle\Attribute\SerializerContext;
use AdrBundle\Response\Contract\ResponderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class JsonResponder implements ResponderInterface, ServiceSubscriberInterface
{
    private ContainerInterface $container;

    #[Required]
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container ?? null;
        $this->container = $container;

        return $previous;
    }

    public function __invoke(mixed $data, array $attributes, array $responseArguments): JsonResponse
    {
        if ($this->container->has('serializer')) {
            $responseArguments['json'] = true;
            $context = [];
            if (\array_key_exists(SerializerContext::class, $attributes)) {
                /** @var SerializerContext $contextAttribute */
                $contextAttribute = $attributes[SerializerContext::class];
                $context = $contextAttribute->context;
            }

            $json = $this->container->get('serializer')
                ->serialize($data, 'json', array_merge([
                    'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                    ],
                    $context
                ));

            return new JsonResponse($json, ...$responseArguments);
        }

        return new JsonResponse($data, ...$responseArguments);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'serializer' => '?'.SerializerInterface::class,
        ];
    }
}
