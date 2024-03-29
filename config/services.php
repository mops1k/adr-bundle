<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AdrBundle\Controller\ActionControllerResolver;
use AdrBundle\Response\Responder\DefaultResponder;
use AdrBundle\Response\Responder\FileResponder;
use AdrBundle\Response\Responder\JsonResponder;
use AdrBundle\Response\Responder\RedirectResponder;
use AdrBundle\Response\Responder\TemplatingResponder;
use AdrBundle\Response\ResponseResolver;
use AdrBundle\Routing\ActionDirectoryRouteLoader;
use AdrBundle\Routing\ActionFileRouteLoader;
use Psr\Container\ContainerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DefaultResponder::class)->tag('adr.action.responder');

    $services->set(FileResponder::class)->tag('adr.action.responder');

    $services->set(RedirectResponder::class)->tag('adr.action.responder');

    $services->set(JsonResponder::class)
        ->public()
        ->call('setContainer', [service(ContainerInterface::class)])
        ->tag('adr.action.responder')
        ->tag('container.service_subscriber');

    $services->set(TemplatingResponder::class)
        ->public()
        ->call('setContainer', [service(ContainerInterface::class)])
        ->tag('adr.action.responder')
        ->tag('container.service_subscriber');

    $services->set(ResponseResolver::class)
        ->arg('$responders', tagged_iterator('adr.action.responder'))
        ->arg('$dispatcher', service('event_dispatcher'));

    $services->set(ActionFileRouteLoader::class)
        ->args([
            '$locator' => service('file_locator'),
            '$loader' => service('routing.loader.attribute'),
        ])
        ->tag('routing.loader');

    $services->set(ActionDirectoryRouteLoader::class)
        ->args([
            '$locator' => service('file_locator'),
            '$loader' => service('routing.loader.attribute'),
        ])
        ->tag('routing.loader');

    $services->set(ActionControllerResolver::class)
        ->args([
            '$container' => service('service_container'),
            '$requestMatcher' => service('router'),
            '$argumentResolver' => service('argument_resolver'),
            '$responseResolver' => service(ResponseResolver::class),
        ])
        ->tag('controller.service_arguments');
};
