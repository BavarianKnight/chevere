<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Route;

use LogicException;
use InvalidArgumentException;
use Chevere\Message;
use Chevere\Path;
use Chevere\Route\src\KeyValidation;
use Chevere\Route\src\Wildcards;
use Chevere\Route\src\WildcardValidation;
use Chevere\Controllers\HeadController;
use Chevere\Utility\Str;
use Chevere\Contracts\Route\RouteContract;
use Chevere\Contracts\HttpFoundation\MethodsContract;
use Chevere\Contracts\HttpFoundation\MethodContract;
use Chevere\HttpFoundation\Method;

// IDEA Route lock (disables further modification)
// IDEA: Reg events, determine who changes a route.
// IDEA: Enable alt routes [/taken, /also-taken, /availabe]
// IDEA: L10n support

final class Route implements RouteContract
{
    /** @const string Route without wildcards. */
    const TYPE_STATIC = 'static';

    /** @const string Route containing wildcards. */
    const TYPE_DYNAMIC = 'dynamic';

    /** @const string Regex pattern used by default (no explicit where). */
    const REGEX_WILDCARD_WHERE = '[A-z0-9\_\-\%]+';

    /** @const string Regex pattern used to detect {wildcard} and {wildcard?}. */
    const REGEX_WILDCARD_SEARCH = '/{([a-z\_][\w_]*\??)}/i';

    /** @const string Regex pattern used to validate route name. */
    const REGEX_NAME = '/^[\w\-\.]+$/i';

    /** @var string Route id relative to the ArrayFile */
    public $id;

    /** @var string Route uri like /api/endpoint/{var?} */
    public $uri;

    /** @var string Route name (if any, must be unique) */
    public $name;

    /** @var array Where clauses based on wildcards */
    public $wheres;

    /** @var array ['method' => 'controller',] */
    public $methods;

    /** @var array [MiddlewareContract,] */
    public $middlewares;

    /** @var array */
    public $wildcards;

    /** @var string Key set representation */
    public $set;

    /** @var array An array containing all the key sets for the route (optionals combo) */
    public $powerSet;

    /** @var array An array containg details about the Route maker */
    public $maker;

    /** @var string */
    public $regex;

    /** @var string */
    public $type;

    public function __construct(string $uri, string $controller = null)
    {
        $this->uri = $uri;
        $keyValidation = new KeyValidation($this->uri);
        $this->maker = $this->getMakerData();
        if ($keyValidation->hasHandlebars()) {
            $wildcards = new Wildcards($this->uri);
            $this->set = $wildcards->set;
            $this->powerSet = $wildcards->powerSet;
            $this->wildcards = $wildcards->wildcards;
        } else {
            $this->set = $this->uri;
        }
        $this->handleType();
        if (isset($controller)) {
            $this->setMethod(new Method('GET', $controller));
        }
    }

    public function setName(string $name): RouteContract
    {
        // Validate $name
        if (!preg_match(static::REGEX_NAME, $name)) {
            throw new InvalidArgumentException(
                (new Message("Expecting at least one alphanumeric, underscore, hypen or dot character. String '%s' provided."))
                    ->code('%s', $name)
                    ->code('%p', static::REGEX_NAME)
                    ->toString()
            );
        }
        $this->name = $name;

        return $this;
    }

    public function setWhere(string $wildcardName, string $regex): RouteContract
    {
        new WildcardValidation($wildcardName, $regex, $this);
        $this->wheres[$wildcardName] = $regex;

        return $this;
    }

    public function setWheres(array $wildcardsPatterns): RouteContract
    {
        foreach ($wildcardsPatterns as $wildcardName => $regexPattern) {
            $this->setWhere($wildcardName, $regexPattern);
        }

        return $this;
    }

    public function setMethod(MethodContract $method): RouteContract
    {
        if (isset($this->methods[$method->method()])) {
            throw new InvalidArgumentException(
                (new Message('Method %s has been already registered.'))
                    ->code('%s', $method->method())->toString()
            );
        }
        $this->methods[$method->method()] = $method->controller();

        return $this;
    }

    public function setMethods(MethodsContract $methods): RouteContract
    {
        foreach ($methods as $method) {
            $this->setMethod($method);
        }

        return $this;
    }

    public function setId(string $id): RouteContract
    {
        $this->id = $id;

        return $this;
    }

    public function addMiddleware(string $callable): RouteContract
    {
        // $this->middlewares[] = $this->getCallableSome($callable);
        $this->middlewares[] = $callable;

        return $this;
    }

    public function getController(string $httpMethod): string
    {
        $controller = $this->methods[$httpMethod];
        if (!isset($controller)) {
            throw new LogicException(
                (new Message('No controller is associated to HTTP method %s.'))
                    ->code('%s', $httpMethod)
                    ->toString()
            );
        }

        return $controller;
    }

    public function fill(): RouteContract
    {
        if (isset($this->wildcards)) {
            foreach ($this->wildcards as $k => $v) {
                if (!isset($this->wheres[$v])) {
                    $this->wheres[$v] = static::REGEX_WILDCARD_WHERE;
                }
            }
        }
        if (isset($this->methods['GET']) && !isset($this->methods['HEAD'])) {
            $this->setMethod(new Method('HEAD', HeadController::class));
        }
        $this->regex = $this->regex();

        return $this;
    }

    public function regex(?string $set = null): string
    {
        $regex = $set ?? ($this->set ?? $this->uri);
        if (!isset($regex)) {
            throw new LogicException(
                (new Message('Unable to process regex for empty regex (no uri).'))->toString()
            );
        }
        $regex = '^'.$regex.'$';
        if (!Str::contains('{', $regex)) {
            return $regex;
        }
        if (isset($this->wildcards)) {
            foreach ($this->wildcards as $k => $v) {
                $regex = str_replace("{{$k}}", '('.$this->wheres[$v].')', $regex);
            }
        }

        return $regex;
    }

    /**
     * Binds a Route object.
     *
     * @param string $key      route key
     * @param string $callable Callable string
     */
    // public static function bind(string $key, string $callable = null, string $rootContext = null): self
    // {
    //     return new static(...func_get_args());
    // }

    private function getMakerData(): array
    {
        $maker = debug_backtrace(0, 3)[2];
        $maker['file'] = Path::relative($maker['file']);

        return $maker;
    }

    private function handleType()
    {
        if (!isset($this->set)) {
            $this->type = Route::TYPE_STATIC;
        } else {
            // Sets (optionals) are like /route/{0}
            $pregReplace = preg_replace('/{[0-9]+}/', '', $this->set);
            if (null != $pregReplace) {
                $pregReplace = trim(Path::normalize($pregReplace), '/');
            }
            $this->type = isset($pregReplace) ? Route::TYPE_DYNAMIC : Route::TYPE_STATIC;
        }
    }
}
