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

namespace Chevere\Components\Path\Contracts;

interface PathContract
{
    /**
     * Return absolute path
     */
    public function absolute(): string;

    // /**
    //  * Returns a boolean indicating whether the path is a stream.
    //  */
    // public function isStream(): bool;

    /**
     * Returns a boolean indicating whether the path exists.
     */
    public function exists(): bool;

    /**
     * Returns a boolean indicating whether the path is a directory and exists.
     */
    public function isDir(): bool;

    /**
     * Returns a boolean indicating whether the path is a file and exists.
     */
    public function isFile(): bool;

    /**
     * Get a child path as a PathContract
     */
    public function getChild(string $path): PathContract;
}