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

namespace Chevere\Components\Router\Interfaces;

use Chevere\Components\Router\Exceptions\RoutePathExistsException;
use Chevere\Components\Router\Exceptions\RouteKeyConflictException;
use Chevere\Components\Router\Exceptions\RouteNameConflictException;
use Chevere\Components\Router\Exceptions\RouterMakerException;

interface RouterMakerInterface
{
    /**
     * Provides access to the RouterPropertiesContract instance.
     */
    public function properties(): RouterPropertiesInterface;

    /**
     * Return an instance with the specified added RouteableContract.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified added RouteableContract.
     *
     * @throws RouterMakerException       if unable to process routing
     * @throws RoutePathExistsException   if $routeable has been already routed
     * @throws RouteKeyConflictException  if $routeable conflicts with other RouteableContract
     * @throws RouteNameConflictException if $routeable name conflicts with other RouteableContract
     */
    public function withAddedRouteable(RouteableInterface $routeable, string $group): RouterMakerInterface;
}