<?php

namespace Wiredupdev\MenuManagerBundle\Tests\Unit\Menu\UriGenerator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\DirectLinkGenerator;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\GeneratorType;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\RouteLinkGenerator;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\UriGeneratorFactory;

#[CoversClass(UriGeneratorFactory::class)]
class UriGeneratorFactoryTest extends TestCase
{
    public function testCreateDirectLink(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('https://www.example.com/home');

        $request = Request::create('/home', 'GET', [], [], [], ['HTTP_HOST' => 'www.example.com', 'HTTPS' => 'on']);
        $request->attributes->add(['_route' => 'app.home']);
        $requestStack = $this->createMock(RequestStack::class);

        $requestStack->expects($this->atLeastOnce())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $factory = new UriGeneratorFactory($urlGenerator, $requestStack);
        $directLink = $factory->create(GeneratorType::DIRECT_LINK_TYPE, 'https://www.example.com/home');
        $this->assertInstanceOf(DirectLinkGenerator::class, $directLink);
        $this->assertEquals('https://www.example.com/home', $directLink->generate());
        $this->assertTrue($directLink->isActive());
    }

    public function testCreateRouteLink(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('https://www.example.com/home');

        $request = Request::create('/home', 'GET', [], [], [], ['HTTP_HOST' => 'www.example.com', 'HTTPS' => 'on']);
        $request->attributes->add(['_route' => 'app.home']);
        $requestStack = $this->createMock(RequestStack::class);

        $requestStack->expects($this->atLeastOnce())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $factory = new UriGeneratorFactory($urlGenerator, $requestStack);
        $directLink = $factory->create(GeneratorType::ROUTE_LINK_TYPE, 'app.home');
        $this->assertInstanceOf(RouteLinkGenerator::class, $directLink);
        $this->assertEquals('https://www.example.com/home', $directLink->generate());
        $this->assertTrue($directLink->isActive());
    }

    public function testCreateFromOptions(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('https://www.example.com/home');

        $request = Request::create('/home', 'GET', [], [], [], ['HTTP_HOST' => 'www.example.com', 'HTTPS' => 'on']);
        $request->attributes->add(['_route' => 'app.home']);
        $requestStack = $this->createMock(RequestStack::class);

        $requestStack->expects($this->atLeastOnce())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $factory = new UriGeneratorFactory($urlGenerator, $requestStack);

        $uriGenerator = $factory->createFromOptions(['route' => ['name' => 'app.home']]);

        $this->assertInstanceOf(RouteLinkGenerator::class, $uriGenerator);
        $this->assertTrue($uriGenerator->isActive());
    }
}
