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

namespace Chevere\Interfaces\Spec\Specs;

use Chevere\Interfaces\Route\RouteEndpointInterface;
use Chevere\Interfaces\Spec\SpecInterface;
use Chevere\Interfaces\Spec\SpecPathInterface;

interface RouteEndpointSpecInterface extends SpecInterface
{
    public function __construct(SpecPathInterface $specRoutePath, RouteEndpointInterface $routeEndpoint);
}