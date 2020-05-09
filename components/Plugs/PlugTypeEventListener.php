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

namespace Chevere\Components\Plugs;

use Chevere\Components\Events\EventsListenerQueue;
use Chevere\Components\Plugs\Interfaces\PlugTypeInterface;

final class PlugTypeEventListener implements PlugTypeInterface
{
    public function trailingName(): string
    {
        return 'EventListener.php';
    }

    public function getQueue(): PlugsQueue
    {
        return new EventsListenerQueue;
    }

    public function plugableAnchorsMethod(): string
    {
        return 'getEventAnchors';
    }
}
