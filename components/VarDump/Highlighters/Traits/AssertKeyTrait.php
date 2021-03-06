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

namespace Chevere\Components\VarDump\Highlighters\Traits;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\OutOfRangeException;

trait AssertKeyTrait
{
    protected function assertKey(string $key): void
    {
        if (!array_key_exists($key, $this->pallet())) {
            throw new OutOfRangeException(
                (new Message('Invalid key %keyName%, expecting one of the following pallet keys: %keys%'))
                    ->code('%keyName%', $key)
                    ->code('%keys%', implode(', ', array_keys($this->pallet())))
            );
        }
    }

    abstract public function pallet(): array;
}
