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

namespace Chevere\Components\Spec;

use Chevere\Components\DataStructures\Traits\DsMapTrait;

final class SpecMethods
{
    use DsMapTrait;

    public function put(string $name, string $jsonPath): void
    {
        $this->map->put($name, $jsonPath);
    }

    public function hasKey(string $name): bool
    {
        return $this->map->hasKey($name);
    }

    public function get(string $name): string
    {
        return $this->map->get($name);
    }
}
