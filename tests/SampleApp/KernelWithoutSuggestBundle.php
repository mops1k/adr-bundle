<?php

namespace AdrBundle\Test\SampleApp;

use AdrBundle\AdrBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\ContainerLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

class KernelWithoutSuggestBundle extends Kernel
{
    #[\Override]
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new AdrBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('kernel.secret', 'null');
            $container->register('kernel', self::class)
                ->addTag('controller.service_arguments')
                ->setAutoconfigured(true)
                ->setSynthetic(true)
                ->setPublic(true)
            ;

            $kernelDefinition = $container->getDefinition('kernel');
            $kernelDefinition->addTag('routing.route_loader');

            $container->addObjectResource($this);

            $container->setParameter('kernel.environment', 'test');
            $container->prependExtensionConfig('framework', [
                'test' => true,
                'serializer' => null,
                'router' => [
                    'resource' => 'kernel::loadRoutes',
                    'type' => 'service',
                ],
            ]);
        });
    }

    public function loadRoutes(ContainerLoader $loader): RouteCollection
    {
        $file = (new \ReflectionObject($this))->getFileName();
        $collection = new RouteCollection();
        if (false === $file) {
            return $collection;
        }
        /* @var PhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file, 'php');
        if (!$kernelLoader instanceof PhpFileLoader) {
            return $collection;
        }
        $configurator = new RoutingConfigurator($collection, $kernelLoader, $file, $file, 'test');

        $finder = (new Finder())
            ->in(__DIR__.'/Action/')
            ->name('*.php')
            ->files();

        foreach ($finder as $file) {
            $configurator->import(
                $file->getRealPath(),
                'action'
            );
        }

        return $collection;
    }
}
