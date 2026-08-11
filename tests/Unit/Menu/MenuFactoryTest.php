<?php

namespace Wiredupdev\MenuManagerBundle\Tests\Unit\Menu;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wiredupdev\MenuManagerBundle\Menu\MenuFactory;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\UriGeneratorFactory;

#[CoversClass(MenuFactory::class)]
class MenuFactoryTest extends TestCase
{
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
    }

    public function testMenuCreationSuccessfully(): void
    {
        $menu = $this->menuFactory->create('main_menu', [
            'children' => [
                [
                    'id' => 'home',
                    'label' => 'Home',
                    'uri' => [
                        'route' => ['name' => 'app.home'],
                    ],
                    'attributes' => [
                        'html' => ['class' => 'active'],
                    ],
                ],
                [
                    'id' => 'partner_product',
                    'label' => 'Partner product',
                    'uri' => [
                        'link' => 'https://www.example.com/product-partner',
                    ],
                    'children' => [
                        [
                            'id' => 'partner_product_1',
                            'label' => 'Partner product 1',
                            'uri' => [
                                'link' => 'https://www.example.com/product-partner_1',
                            ],
                            'children' => [
                                [
                                    'id' => 'partner_product_1_1',
                                    'label' => 'Partner product 1 1',
                                    'uri' => [
                                        'link' => 'https://www.example.com/product-partner_1_1',
                                    ],
                                ]
                            ]
                        ],
                    ]
                ],
            ],
        ]);

        $this->assertEquals('main_menu', $menu->getId());
        $this->assertEquals('https://www.example.com/home', $menu->getChild('home')->getUri());
    }
}
