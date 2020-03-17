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

namespace Chevere\Components\Api;

use Chevere\Components\Controller\ControllerName;
use Chevere\Components\Controllers\Api\HeadController;
use Chevere\Components\Controllers\Api\OptionsController;
use Chevere\Components\Http\Interfaces\RouteEndpointsInterface;
use Chevere\Components\Http\Method;
use Chevere\Components\Http\MethodController;
use Chevere\Components\Route\Interfaces\RouteEndpointInterface;
use Chevere\Components\Route\RouteEndpoint;

final class ApiEndpoint implements RouteEndpointInterface
{
    /** @var array */
    private $array;

    /** @var RouteEndpointsInterface */
    private $methodControllerNameCollection;

    /**
     *
     */
    public function __construct(RouteEndpointsInterface $collection)
    {
        $this->array = [];
        $this->methodControllerNameCollection = $collection;
        $this->fillEndpointOptions();
        $this->autofillMissingOptionsHead();
    }

    public function methodControllerNameCollection(): RouteEndpointsInterface
    {
        return $this->methodControllerNameCollection;
    }

    public function toArray(): array
    {
        return $this->array;
    }

    private function fillEndpointOptions(): void
    {
        foreach ($this->methodControllerNameCollection->toArray() as $method) {
            $httpMethod = $method->method();
            $controllerClassName = $method->controllerName();
            $httpMethodOptions = [];
            $httpMethodOptions['description'] = $controllerClassName::description();
            $controllerParameters = $controllerClassName::parameters();
            if (isset($controllerParameters)) {
                $httpMethodOptions['parameters'] = $controllerParameters;
            }
            $this->array['OPTIONS'][$httpMethod] = $httpMethodOptions;
        }
    }

    private function autofillMissingOptionsHead(): void
    {
        foreach ([
            'OPTIONS' => [
                OptionsController::class, [
                    'description' => OptionsController::description(),
                ],
            ],
            'HEAD' => [
                HeadController::class, [
                    'description' => HeadController::description(),
                ],
            ],
        ] as $k => $v) {
            if (!$this->methodControllerNameCollection->hasMethod(new Method($k))) {
                $this->methodControllerNameCollection = $this->methodControllerNameCollection
                    ->withAddedRouteEndpoint(
                        new RouteEndpoint(
                            new Method($k),
                            new ControllerName($v[0])
                        )
                    );
                $this->array['OPTIONS'][$k] = $v[1];
            }
        }
    }
}