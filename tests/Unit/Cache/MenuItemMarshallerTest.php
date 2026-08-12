<?php

namespace Wiredupdev\MenuManagerBundle\Tests\Unit\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wiredupdev\MenuManagerBundle\Cache\MenuItemMarshaller;
use Wiredupdev\MenuManagerBundle\Menu\MenuFactory;
use Wiredupdev\MenuManagerBundle\Menu\MenuItemInterface;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\DirectLinkGenerator;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\UriGeneratorFactory;

#[CoversClass(MenuItemMarshaller::class)]
class MenuItemMarshallerTest extends TestCase
{
    private MenuItemMarshaller $marshaller;

    private MenuFactory $menuFactory;

    protected function setUp(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('https://www.example.com/home');

        $request = Request::create('/home', 'GET', [], [], [], ['HTTP_HOST' => 'www.example.com', 'HTTPS' => 'on']);
        $request->attributes->add(['_route' => 'app.home']);
        $request->attributes->add(['_route' => 'app.contactus']);
        $requestStack = $this->createMock(RequestStack::class);
        $uriGeneratorFactory = new UriGeneratorFactory($urlGenerator, $requestStack);
        $this->menuFactory = new MenuFactory($uriGeneratorFactory);
        $this->marshaller = new MenuItemMarshaller($this->menuFactory);
    }

    public function testMarshallMenuItem(): void
    {
        $menu = [];
        $failed = [];
        $menu[] = $this->menuFactory->create('main_meu', [
            'children' => [
                [
                    'id' => 'home',
                    'label' => 'Home',
                    'uri' => [
                        'link' => 'https://www.example.com/home',
                    ],
                ],
            ],
        ]);

        $expected = [
            serialize(
                [
                    '__is_menu_item' => true,
                    '__menu' => [
                        'id' => 'main_meu',
                        'label' => '',
                        'attributes' => [],
                        'position' => 0,
                        'uri' => null,
                        'children' => [
                            'home' => [
                                'id' => 'home',
                                'label' => 'Home',
                                'attributes' => [],
                                'position' => 1,
                                'uri' => [
                                    'raw' => [
                                        'value' => 'https://www.example.com/home',
                                        'type' => DirectLinkGenerator::class,
                                        'parameters' => [],
                                    ],
                                    'target' => '_self',
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ]
            ),
        ];

        $this->assertEquals($expected, $this->marshaller->marshall($menu, $failed));
    }

    public function testUnmarshall(): void
    {
        $serializedValues = serialize(
            [
                '__is_menu_item' => true,
                '__menu' => [
                    'id' => 'main_meu',
                    'label' => '',
                    'uri' => null,
                    'children' => [
                        'home' => [
                            'id' => 'home',
                            'label' => 'Home',
                            'uri' => [
                                'raw' => [
                                    'value' => 'https://www.example.com/home',
                                    'type' => DirectLinkGenerator::class,
                                    'parameters' => [],
                                ],
                                'target' => '_self',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ]
        );

        $menu = $this->marshaller->unmarshall($serializedValues);

        $this->assertInstanceOf(MenuItemInterface::class, $menu);
    }
}
