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

use InvalidArgumentException;
use RuntimeException;

use Chevere\Components\Message\Message;
use Chevere\Components\Runtime\Traits\Set;
use Chevere\Components\Validate;
use Chevere\Contracts\Runtime\SetContract;

class SetTimeZone implements SetContract
{
    use Set;

    public function set(): void
    {
        if (date_default_timezone_get() == $this->value) {
            return;
        }
        if ('UTC' != $this->value && !Validate::timezone($this->value)) {
            throw new InvalidArgumentException(
                (new Message('Invalid timezone %timezone%'))
                    ->code('%timezone%', $this->value)
                    ->toString()
            );
        }
        if (!@date_default_timezone_set($this->value)) {
            throw new RuntimeException(
                (new Message('False return on %s(%v)'))
                    ->code('%s', 'date_default_timezone_set')
                    ->code('%v', $this->value)
                    ->toString()
            );
        }
    }
}
