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

namespace Chevere\Tests\Routing;

use Chevere\Exceptions\Routing\ExpectingControllerException;
use Chevere\Interfaces\Route\RouteEndpointInterface;
use PHPUnit\Framework\TestCase;
use function Chevere\Components\Filesystem\dirForString;
use function Chevere\Components\Routing\routeEndpointsForDir;

final class RouteEndpointsIteratorTest extends TestCase
{
    public function testObjects(): void
    {
        $dir = dirForString(__DIR__ . '/_resources/routes/articles/{id}/');
        $routeEndpoints = routeEndpointsForDir($dir);
        $this->assertCount(1, $routeEndpoints);
        /** @var string $key */
        foreach ($routeEndpoints->keys() as $key) {
            $this->assertInstanceOf(
                RouteEndpointInterface::class,
                $routeEndpoints->get($key)
            );
        }
    }

    public function testWrongObjects(): void
    {
        $dir = dirForString(__DIR__ . '/_resources/wrong-routes/articles/');
        $this->expectException(ExpectingControllerException::class);
        routeEndpointsForDir($dir);
    }
}
