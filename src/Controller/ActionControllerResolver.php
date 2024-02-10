<?php

namespace AdrBundle\Controller;

use AdrBundle\Response\ResponseResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

final class ActionControllerResolver
{
    public function __construct(
        protected ContainerInterface $container,
        protected RequestMatcherInterface $requestMatcher,
        protected ArgumentResolverInterface $argumentResolver,
        protected ResponseResolver $responseResolver
    ) {
    }

    /**
     * @param class-string|null $_action
     */
    public function __invoke(Request $request, ?string $_action): Response
    {
        $matched = $this->requestMatcher->matchRequest($request);

        if (null === $_action) {
            throw new NotFoundHttpException(\sprintf(
                'Action for route "%s" not found.',
                $matched['_route']
            ));
        }

        $reflectionAction = new \ReflectionClass($_action);
        $action = $reflectionAction->newInstanceWithoutConstructor();
        if ($this->container->has($_action)) {
            $action = $this->container->get($_action);
        }

        if (!\is_callable($action)) {
            throw new \RuntimeException(\sprintf('Action %s must implement method "__invoke".', $action::class));
        }

        $arguments = $this->argumentResolver->getArguments($request, $action);

        return $this->responseResolver->resolve($action, $arguments);
    }
}
