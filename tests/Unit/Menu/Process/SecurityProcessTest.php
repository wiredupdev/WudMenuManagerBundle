<?php

namespace Wiredupdev\MenuManagerBundle\Tests\Unit\Menu\Process;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wiredupdev\MenuManagerBundle\Menu\MenuFactory;
use Wiredupdev\MenuManagerBundle\Menu\MenuItemInterface;
use Wiredupdev\MenuManagerBundle\Menu\Process\SecurityProcess;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\UriGeneratorFactory;

#[CoversClass(SecurityProcess::class)]
class SecurityProcessTest extends TestCase
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private SecurityProcess $securityProcess;

    private MenuFactory $menuFactory;

    protected function setUp(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $authorizationChecker->expects($this->atLeastOnce())->method('isGranted')->willReturn(true, false, true);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('https://www.example.com/home');

        $request = Request::create('/home', 'GET', [], [], [], ['HTTP_HOST' => 'www.example.com', 'HTTPS' => 'on']);
        $request->attributes->add(['_route' => 'app.home']);
        $request->attributes->add(['_route' => 'app.contactus']);
        $requestStack = $this->createMock(RequestStack::class);
        $uriGeneratorFactory = new UriGeneratorFactory($urlGenerator, $requestStack);
        $this->menuFactory = new MenuFactory($uriGeneratorFactory);

        $this->securityProcess = new SecurityProcess($authorizationChecker);
    }

    public function testSecurityProcessWillReturnOnlyMainMenuManagementAndAccountVisibles(): void
    {
        $menu = $this->menuFactory->create('main_menu', [
            'children' => [
                [
                    'id' => 'management',
                    'label' => 'Management',
                    'attributes' => ['security' => ['roles' => ['ROLE_MANAGEMENT']]],
                    'uri' => [
                        'link' => 'https://www.example.com/management',
                    ],
                    'children' => [
                        [
                            'id' => 'account',
                            'label' => 'accounts',
                            'attributes' => ['security' => ['roles' => ['ROLE_MANAGEMENT', 'ROLE_MANAGEMENT_ACCOUNTS']]],
                            'uri' => [
                                'link' => 'https://www.example.com/management/accounts',
                            ],
                        ],
                        [
                            'id' => 'billing',
                            'label' => 'billing',
                            'attributes' => ['security' => ['roles' => ['ROLE_MANAGEMENT', 'ROLE_MANAGEMENT_BILLING']]],
                            'uri' => [
                                'link' => 'https://www.example.com/management/billing',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->securityProcess->process($menu);

        $this->assertSame([
            'main_menu',
            'management',
            'billing',
        ], $this->getMenuVisibleItemsIds($menu));
    }

    private function getMenuVisibleItemsIds(MenuItemInterface $menuItem, array &$items = []): array
    {
        if ($menuItem->isVisible()) {
            $items[] = $menuItem->getId();
        }

        foreach ($menuItem as $child) {
             $this->getMenuVisibleItemsIds($child, $items);
        }

        return $items;
    }
}
