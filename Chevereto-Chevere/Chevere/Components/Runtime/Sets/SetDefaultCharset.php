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

class SetDefaultCharset implements SetContract
{
    use Set;

    public function set(): void
    {
        if (!@ini_set('default_charset', $this->value)) {
            throw new RuntimeException(
                (new Message('Unable to set %s %v'))
                    ->code('%s', 'default_charset')
                    ->code('%v', $this->value)
                    ->toString()
            );
        }
    }
}
