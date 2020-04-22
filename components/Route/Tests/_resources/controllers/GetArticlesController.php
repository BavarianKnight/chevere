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

namespace Chevere\Components\Route\Tests\_resources\controllers;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Controller\Interfaces\ControllerArgumentsInterface;

final class GetArticlesController extends Controller
{
    public function run(ControllerArgumentsInterface $arguments): void
    {
        // does nothing
    }
}
