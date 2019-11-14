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

namespace Chevere\Components\Cache;

use Chevere\Components\Cache\Exceptions\CacheInvalidKeyException;
use Chevere\Components\Message\Message;
use Chevere\Contracts\Cache\CacheKeyContract;

final class CacheKey implements CacheKeyContract
{
    /** @var string */
    private $key;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->assertKeyName();
    }

    /**
     * {@inheritdoc}
     */
    public function key(): string
    {
        return $this->key;
    }

    private function assertKeyName(): void
    {
        if (preg_match_all('#[' . CacheKeyContract::ILLEGAL_KEY_CHARACTERS . ']#', $this->key, $matches)) {
            $matches = array_unique($matches[0]);
            $forbidden = implode(' ', $matches);
            throw new CacheInvalidKeyException(
                (new Message('Use of forbidden character(s) %character%'))
                    ->code('%character%', $forbidden)
                    ->toString()
            );
        }
    }
}