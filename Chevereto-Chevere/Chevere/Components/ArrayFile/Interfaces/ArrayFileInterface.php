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

namespace Chevere\Components\ArrayFile\Interfaces;

use Chevere\Components\File\Interfaces\FileInterface;
use Chevere\Components\File\Interfaces\FilePhpInterface;
use Chevere\Components\Type\Interfaces\TypeInterface;

interface ArrayFileInterface
{
    public function __construct(FilePhpInterface $filePhp);

    /**
     * Return an instance with the specified ServicesContract.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified RouterContract.
     *
     * @param TypeInterface $type a Type that all array top level members must satisfy
     *
     * @throws ArrayFileTypeException if one of the members doesn't match the specified $type
     */
    public function withMembersType(TypeInterface $type): ArrayFileInterface;

    /**
     * Provides access to the FileContract instance in FilePhpContract.
     */
    public function file(): FileInterface;

    /**
     * Provides access to the file return array.
     */
    public function array(): array;
}