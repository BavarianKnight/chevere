<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Runtime\Sets;

use RuntimeException;

use Chevere\Components\Message\Message;
use Chevere\Components\Runtime\Traits\Set;
use Chevere\Contracts\Runtime\SetContract;

class SetDebug implements SetContract
{
    use Set;

    const ACCEPT = [0, 1];

    public function set(): void
    {
        if (!in_array($this->value, static::ACCEPT)) {
            throw new RuntimeException(
                (new Message('Expecting %expecting%, %value% provided'))
                    ->code('%expecting%', implode(', ', static::ACCEPT))
                    ->code('%value%', $this->value)
                    ->toString()
            );
        }
    }
}
