<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Router\Tests;

use Chevere\Components\Cache\Cache;
use Chevere\Components\Controller\ControllerName;
use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Http\Method;
use Chevere\Components\Route\PathUri;
use Chevere\Components\Route\Route;
use Chevere\Components\Route\RouteName;
use Chevere\Components\Router\Exceptions\RouteKeyConflictException;
use Chevere\Components\Router\Exceptions\RouteNameConflictException;
use Chevere\Components\Router\Exceptions\RoutePathExistsException;
use Chevere\Components\Router\Interfaces\RouteableInterface;
use Chevere\Components\Router\Interfaces\RouterCacheInterface;
use Chevere\Components\Router\Interfaces\RouterInterface;
use Chevere\Components\Router\Routeable;
use Chevere\Components\Router\RouterCache;
use Chevere\Components\Router\RouterMaker;
use Chevere\TestApp\App\Controllers\TestController;
use PHPUnit\Framework\TestCase;

final class RouterMakerTest extends TestCase
{
    private RouterCacheInterface $routerCache;

    public function setUp(): void
    {
        $this->routerCache = new RouterCache(
            new Cache(
                new Dir(
                    (new Path(__DIR__))->getChild('_resources')->getChild('working')
                )
            )
        );
    }

    public function tearDown(): void
    {
        $this->routerCache->remove();
        $this->routerCache->routeCache()->remove(0);
    }

    public function testConstruct(): void
    {
        $routerMaker = new RouterMaker($this->routerCache);
        $this->assertInstanceOf(RouterInterface::class, $routerMaker->router());
    }

    public function testWithAddedRouteable(): void
    {
        $routeable = $this->getRouteable('/path', 'PathName');
        $pathUri = $routeable->route()->pathUri();
        $routerMaker = (new RouterMaker($this->routerCache))
            ->withAddedRouteable($routeable, 'group');
        $this->assertTrue($routerMaker->router()->index()->has($pathUri));
    }

    public function testWithAlreadyAddedPath(): void
    {
        $routeable = $this->getRouteable('/path', 'PathName');
        $this->expectException(RoutePathExistsException::class);
        (new RouterMaker($this->routerCache))
            ->withAddedRouteable($routeable, 'group')
            ->withAddedRouteable($routeable, 'another-group');
    }

    public function testWithAlreadyAddedKey(): void
    {
        $routeable1 = $this->getRouteable('/path/{foo}', 'FooName');
        $routeable2 = $this->getRouteable('/path/{bar}', 'BarName');
        $this->expectException(RouteKeyConflictException::class);
        (new RouterMaker($this->routerCache))
            ->withAddedRouteable($routeable1, 'group')
            ->withAddedRouteable($routeable2, 'another-group');
    }

    public function testWithAlreadyAddedName(): void
    {
        $routeable1 = $this->getRouteable('/path1', 'SameName');
        $routeable2 = $this->getRouteable('/path2', 'SameName');
        $this->expectException(RouteNameConflictException::class);
        (new RouterMaker($this->routerCache))
            ->withAddedRouteable($routeable1, 'group')
            ->withAddedRouteable($routeable2, 'another-group');
    }

    private function getRouteable(string $path, string $name = null): RouteableInterface
    {
        $route = new Route(new PathUri($path));
        $route = $route
            ->withAddedMethod(
                new Method('GET'),
                new ControllerName(TestController::class)
            );
        if ($name !== null) {
            $route = $route->withName(new RouteName($name));
        }

        return new Routeable($route);
    }
}