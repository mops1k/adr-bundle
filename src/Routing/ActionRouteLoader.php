<?php

namespace AdrBundle\Routing;

use AdrBundle\Controller\ActionControllerResolver;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\Loader\AttributeFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ActionRouteLoader extends AttributeFileLoader implements RouteLoaderInterface
{
    #[\Override]
    public function load(mixed $file, string $type = null): ?RouteCollection
    {
        /** @var class-string|false $class */
        $class = $this->findClass($file);

        if (false === $class || (!\class_exists($class) && !\method_exists($class, '__invoke'))) {
            return null;
        }
        $routes = new RouteCollection();

        $collectedRoutes = [];

        $reflectionClass = new \ReflectionClass($class);
        $reflectionAttributes = $reflectionClass->getAttributes(RouteAttribute::class);
        if (0 === count($reflectionAttributes)) {
            return null;
        }

        $routeAttributes = [];
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if (!$attribute instanceof RouteAttribute) {
                continue;
            }

            $routeAttributes[] = $attribute;
        }

        if (0 === count($routeAttributes)) {
            return null;
        }

        foreach ($routeAttributes as $attribute) {
            $baseRouteName = $attribute->getName() ?? $class;
            if (null === $attribute->getPath() && count($attribute->getLocalizedPaths()) > 0) {
                foreach ($attribute->getLocalizedPaths() as $path) {
                    $routeName = $baseRouteName;
                    if (\array_key_exists($baseRouteName, $collectedRoutes)) {
                        $routeName = $baseRouteName.'['.str_replace('/', '_', $path).']';
                    }

                    $collectedRoutes[$routeName] = $this->createRoute(
                        $class,
                        $path,
                        $attribute
                    );
                }

                continue;
            }

            if (null === $attribute->getPath()) {
                continue;
            }

            $routeName = $baseRouteName;
            if (\array_key_exists($baseRouteName, $collectedRoutes)) {
                $routeName = $baseRouteName.'['.str_replace('/', '_', $attribute->getPath()).']';
            }

            $collectedRoutes[$routeName] = $this->createRoute($class, $attribute->getPath(), $attribute);
        }

        foreach ($collectedRoutes as $name => $route) {
            $routes->add($name, $route);
        }

        return $routes;
    }

    #[\Override]
    public function supports($resource, string $type = null): bool
    {
        return \is_file($resource) && ('action' === $type || 'adr' === $type);
    }

    private function createRoute(string $actionClass, string $path, RouteAttribute $attribute): Route
    {
        $defaults = $attribute->getDefaults();
        $defaults['_controller'] = ActionControllerResolver::class;
        $defaults['_action'] = $actionClass;

        return new Route(
            path: $path,
            defaults: $defaults,
            requirements: $attribute->getRequirements(),
            options: $attribute->getOptions(),
            host: $attribute->getHost(),
            schemes: $attribute->getSchemes(),
            methods: $attribute->getMethods(),
            condition: $attribute->getCondition(),
        );
    }
}
