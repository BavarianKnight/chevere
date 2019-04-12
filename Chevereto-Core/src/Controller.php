<?php

declare(strict_types=1);

/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Core;

use Exception;

// Define a hookable code entry:
// $this->hook('myHook', function ($that) use ($var) {
//     $that->bar = 'foo'; // $that is $this (the controller instance)
//     $var = 'foobar'; // Alters $var since it hass been passed by the 'use' constructor.
// });

// Hooks for 'myHook' should be defined using:
// Hook::bind('myHook@controller:file', Hook::BEFORE, function ($that) {
//     $that->source .= ' nosehaceeso no';
// });

/**
 * Controller is the defacto controller in Chevereto\Core.
 */
abstract class Controller implements Interfaces\ControllerInterface
{
    use Traits\HookableTrait;

    const TYPE_DECLARATIONS = ['array', 'callable', 'bool', 'float', 'int', 'string', 'iterable'];
    const OPTIONS = [];

    /** @var App */
    private $app;

    /** @var string|null Controller description */
    protected static $description = null;

    /** @var array|null Controller resources [propName => className] */
    protected static $resources;

    /** @var array|null Parameters passed via headers */
    protected static $parameters;

    /**
     * Automatically create the defined Controller RESOURCES.
     */
    public function __construct()
    {
        // foreach (static::RESOURCES as $property => $className) {
        //     // A valid resource must have a constructor like __construct(string $var)
        //     $this->{$property} = new $className($this->getArgument($property));
        // }
    }

    public function getRoute(): ?Route
    {
        return $this->getApp()->getRoute();
    }

    public function getApi(): ?Api
    {
        return $this->getApp()->getApi();
    }

    public function setResponse(Response $response): Interfaces\ControllerInterface
    {
        $this->getApp()->setResponse($response);

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->getApp()->getResponse();
    }

    public function setApp(App $app): Interfaces\ControllerInterface
    {
        $this->app = $app;

        return $this;
    }

    public function getApp(): App
    {
        return $this->app;
    }

    public function invoke(string $controller, $parameters = null)
    {
        if (gettype($parameters) != 'array') {
            $parameters = [$parameters];
        }
        if (class_exists($controller)) {
            // $r = new ReflectionClass($controller);
            // if (!$r->hasMethod('__invoke')) {
            //     throw new ControllerException(
            //         (new Message("Missing %s method in class %c"))
            //         ->code('%s', '__invoke')
            //         ->code('%c', $controller)
            //     );
            // }
            $that = new $controller();
        } else {
            $controllerArgs = [$controller];
            if (Utils\Str::startsWith('@', $controller)) {
                $context = dirname(debug_backtrace(0, 1)[0]['file']);
                $controllerArgs = [substr($controller, 1), $context];
            }
            $filename = Path::fromHandle(...$controllerArgs);
            if (!File::exists($filename)) {
                throw new Exception(
                    (new Message("Unable to invoke controller %s (filename doesn't exists)."))
                    ->code('%s', $filename)
                );
            }
            $that = Load::php($filename);
        }
        if (!is_callable($that)) {
            throw new Exception(
                (new Message('Expected %s callable, %t provided.'))
                    ->code('%s', '$controller')
                    ->code('%t', gettype($controller))
            );
        }
        // Pass this to that so you can this while you that dawg!
        foreach (get_object_vars($this) as $k => $v) {
            $that->{$k} = $v;
        }

        return $that(...$parameters);
    }

    public function setDescription(string $description = null): self
    {
        if (isset($description)) {
            $this->setDescription = $description;
        }

        return $this;
    }

    public function __invoke()
    {
        throw new LogicException(
            (string)
                (new Message('Class %c Must implement its own %s method.'))
                    ->code()
        );
    }

    final public static function getDescription(): ?string
    {
        return static::$description;
    }

    final public static function getResources(): ?array
    {
        return static::$resources;
    }

    final public static function getParameters(): ?array
    {
        return static::$parameters;
    }
}
class ControllerException extends CoreException
{
}
