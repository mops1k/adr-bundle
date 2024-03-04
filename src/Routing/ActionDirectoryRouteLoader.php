<?php

namespace AdrBundle\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

class ActionDirectoryRouteLoader extends ActionFileRouteLoader implements RouteLoaderInterface
{
    #[\Override]
    public function load(mixed $directory, string $type = null): ?RouteCollection
    {
        $directoryRouteCollection = new RouteCollection();

        $files = (new Finder())
            ->in($directory)
            ->name('*.php')
            ->files();

        foreach ($files as $file) {
            if (!parent::supports($file->getRealPath(), $type)) {
                continue;
            }
            $collection = parent::load($file, $type);

            if (null === $collection) {
                continue;
            }
            $directoryRouteCollection->addCollection($collection);
        }

        return $directoryRouteCollection;
    }

    #[\Override]
    public function supports($resource, string $type = null): bool
    {
        return \is_dir($resource) && ('action' === $type || 'adr' === $type);
    }
}
