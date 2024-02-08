<?php

namespace AdrBundle\Response\Responder;

use AdrBundle\Response\Contract\ResponderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Environment;

readonly class TemplatingResponder implements ResponderInterface, ServiceSubscriberInterface
{
    private ContainerInterface $container;

    #[Required]
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container ?? null;
        $this->container = $container;

        return $previous;
    }

    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response
    {
        if (null === $this->container) {
            throw new \LogicException('Service container not found.');
        }

        if (!$this->container?->has('twig')) {
            throw new \LogicException(\sprintf(
                'You cannot use the "%s" responder if the Twig Bundle is not available. Try running "composer require symfony/twig-bundle".',
                static::class
            ));
        }

        $twig = $this->container?->get('twig');

        if (!\array_key_exists(Template::class, $attributes)) {
            throw new \RuntimeException('Template path for action not configured.');
        }

        if (!is_array($data)) {
            throw new \LogicException(\sprintf(
                'Action with TemplatingResponder must return array, %s given.',
                gettype($data)
            ));
        }
        /** @var Template $templateAttribute */
        $templateAttribute = $attributes[Template::class];
        $vars = array_merge_recursive($templateAttribute->vars ?? [], $data);

        if (false === $templateAttribute->stream) {
            return new Response($twig->render($templateAttribute->template, $vars), ...$responseArguments);
        }

        $callback = fn () => $twig->display($templateAttribute->template, $vars);

        return new StreamedResponse($callback, ...$responseArguments);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'twig' => '?'.Environment::class,
        ];
    }
}
