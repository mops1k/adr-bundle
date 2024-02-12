<?php

namespace AdrBundle\Test\Functional;

use AdrBundle\Test\SampleApp\KernelWithoutSuggestBundle;
use AdrBundle\Test\SampleApp\KernelWithSuggestBundle;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \AdrBundle\Attribute\Responder
 * @covers \AdrBundle\Attribute\ContentDispositionType
 * @covers \AdrBundle\Attribute\SerializerContext
 * @covers \AdrBundle\Controller\ActionControllerResolver
 * @covers \AdrBundle\Event\PostRespondEvent
 * @covers \AdrBundle\Response\ResponseResolver
 * @covers \AdrBundle\Response\Responder\DefaultResponder
 * @covers \AdrBundle\Response\Responder\JsonResponder
 * @covers \AdrBundle\Response\Responder\TemplatingResponder
 * @covers \AdrBundle\Response\Responder\FileResponder
 * @covers  \AdrBundle\Routing\ActionRouteLoader
 */
class ActionTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return KernelWithoutSuggestBundle::class;
    }

    /**
     * @param class-string<KernelInterface>     $kernelClass
     * @param callable(Response $response):void $assertCallable
     */
    #[DataProvider('requestProvider')]
    public function testAction(string $kernelClass, Request $request, callable $assertCallable): void
    {
        self::$class = $kernelClass;
        self::bootKernel();
        $response = self::$kernel?->handle($request);
        if (!$response instanceof Response) {
            self::fail();
        }
        $assertCallable($response);
    }

    /**
     * @return \Generator
     */
    public static function requestProvider(): iterable
    {
        yield 'DefaultResponder' => [
            'kernelClass' => KernelWithoutSuggestBundle::class,
            'request' => Request::create('/default'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(200, $response->getStatusCode());
            },
        ];
        yield 'JsonResponder as array' => [
            'kernelClass' => KernelWithoutSuggestBundle::class,
            'request' => Request::create('/json-array'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(200, $response->getStatusCode());
                self::assertJson($response->getContent() ?: '');

                $responseArray = \json_decode($response->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
                self::assertEquals(['success' => true], $responseArray);
            },
        ];
        yield 'JsonResponder as object fail' => [
            'kernelClass' => KernelWithoutSuggestBundle::class,
            'request' => Request::create('/json-object'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(500, $response->getStatusCode());
            },
        ];
        // yield 'JsonResponder as object success' => [
        //     'kernelClass' => KernelWithSuggestBundle::class,
        //     'request' => Request::create('/json-object'),
        //     'assertCallable' => function (Response $response): void {
        //         self::assertEquals(200, $response->getStatusCode());
        //         self::assertJson($response->getContent());
        //
        //         $responseArray = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        //         self::assertEquals(['status' => true], $responseArray);
        //     },
        // ];
        yield 'FileResponder' => [
            'kernelClass' => KernelWithoutSuggestBundle::class,
            'request' => Request::create('/file'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(200, $response->getStatusCode());
                self::assertEquals('attachment; filename=sample.txt', $response->headers->get('content-disposition'));
                self::assertEquals('text/plain; charset=UTF-8', $response->headers->get('content-type'));
            },
        ];
        yield 'TemplatingResponder fail' => [
            'kernelClass' => KernelWithoutSuggestBundle::class,
            'request' => Request::create('/templating'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(500, $response->getStatusCode());
            },
        ];
        yield 'TemplatingResponder success' => [
            'kernelClass' => KernelWithSuggestBundle::class,
            'request' => Request::create('/templating'),
            'assertCallable' => function (Response $response): void {
                self::assertEquals(200, $response->getStatusCode());
            },
        ];
    }
}
