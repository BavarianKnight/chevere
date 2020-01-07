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

namespace Chevere\Contracts\Path;

use Chevere\Components\Path\Exceptions\PathInvalidException;

interface CheckFormatContract
{
    /**
     * @throws PathInvalidException if the $path format is invalid
     */
    public function __construct(string $path);

    /**
     * @throws PathInvalidException if the $path is relative
     */
    public function assertNotRelativePath(): void;
}
