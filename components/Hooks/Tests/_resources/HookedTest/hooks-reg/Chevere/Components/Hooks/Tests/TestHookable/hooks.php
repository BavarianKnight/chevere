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

use Chevere\Components\Hooks\Tests\_resources\TestHook;
use Chevere\Components\Plugs\PlugsQueue;
use Chevere\Components\Plugs\Types\HookPlugType;

return (new PlugsQueue(new HookPlugType))
    ->withAddedPlug(new TestHook);
