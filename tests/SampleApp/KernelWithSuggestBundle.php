<?php

namespace AdrBundle\Test\SampleApp;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KernelWithSuggestBundle extends KernelWithoutSuggestBundle
{
    public function registerBundles(): iterable
    {
        $bundles = parent::registerBundles();
        /* @phpstan-ignore-next-line */
        $bundles[] = new TwigBundle();

        return $bundles;
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $container->prependExtensionConfig('framework', [
                'serializer' => [
                    'enabled' => true,
                ],
            ]);
            $container->prependExtensionConfig('twig', [
                'default_path' => __DIR__.'/Resources/templates',
            ]);
        });
    }
}
