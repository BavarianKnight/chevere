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

namespace Chevere\Interfaces\Plugin;

use Chevere\Components\Plugin\PlugsQueue;

interface TypedPlugsQueueInterface
{
    /**
     * @return string The accepted plug interface.
     */
    public function accept(): string;

    public function queue(): PlugsQueue;
}