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

namespace Chevere\Tests\Plugin;

use Chevere\Components\Plugin\PlugsQueue;
use Chevere\Components\Plugin\Types\EventListenerPlugType;
use Chevere\Components\Plugin\Types\HookPlugType;
use Chevere\Exceptions\Core\OverflowException;
use Chevere\Exceptions\Plugin\PlugInterfaceException;
use Chevere\Tests\Plugin\_resources\src\TestHook;
use LogicException;
use PHPUnit\Framework\TestCase;

final class PlugsQueueTest extends TestCase
{
    public function testConstruct(): void
    {
        $plugType = new HookPlugType;
        $plugsQueue = new PlugsQueue($plugType);
        $this->assertSame($plugType, $plugsQueue->plugType());
        $this->assertSame([], $plugsQueue->toArray());
    }

    public function testWithWrongPlug(): void
    {
        $hook = new TestHook;
        $plugType = new EventListenerPlugType;
        $plugsQueue = new PlugsQueue($plugType);
        $this->expectException(PlugInterfaceException::class);
        $plugsQueue->withAdded($hook);
    }

    public function testWithPlug(): void
    {
        $hook = new TestHook;
        $plugQueue = new PlugsQueue(new HookPlugType);
        $plugQueue = $plugQueue->withAdded($hook);
        $this->assertSame([
            $hook->anchor() => [
                0 => [
                    get_class($hook)
                ]
            ]
        ], $plugQueue->toArray());
    }

    public function testWithAlreadyAddedPlug(): void
    {
        $hook = new TestHook;
        $plugsQueue = (new PlugsQueue(new HookPlugType))
            ->withAdded($hook);
        $this->expectException(OverflowException::class);
        $plugsQueue->withAdded($hook);
    }
}
