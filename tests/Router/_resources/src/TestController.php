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

namespace Chevere\Tests\Router\_resources\src;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

final class TestController extends Controller
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('name'))
                    ->withRegex(new Regex('/\w+/'))
            )
            ->withAdded(
                (new ParameterRequired('id'))
                    ->withRegex(new Regex('/\d+/'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        return new ResponseSuccess([]);
    }
}
