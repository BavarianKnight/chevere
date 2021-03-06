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

namespace Chevere\Interfaces\ClassMap;

use Chevere\Exceptions\ClassMap\ClassNotExistsException;
use Chevere\Exceptions\ClassMap\ClassNotMappedException;
use Chevere\Exceptions\ClassMap\StringMappedException;
use Chevere\Interfaces\To\ToArrayInterface;
use Countable;

/**
 * Describes the component in charge of mapping classes to strings.
 */
interface ClassMapInterface extends Countable, ToArrayInterface
{
    /**
     * Return an instance with the specified className mapping.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified className mapping.
     *
     * @throws ClassNotExistsException
     * @throws StringMappedException
     */
    public function withPut(string $className, string $string): ClassMapInterface;

    /**
     * Indicates whether the instance is mapping the given class name.
     */
    public function has(string $className): bool;

    /**
     * Provides access to the class name mapping.
     *
     * @throws ClassNotMappedException
     */
    public function get(string $className): string;

    /**
     * Provides access to the class map.
     *
     * ```php
     * return [
     *     'className' => 'string',
     * ]
     * ```
     */
    public function toArray(): array;
}
