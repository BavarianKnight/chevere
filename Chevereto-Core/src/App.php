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
use ReflectionMethod;
use ReflectionFunction;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;

// use Symfony\Component\HttpFoundation\Response;

class App extends Container
{
    use Traits\CallableTrait;

    const NAMESPACES = ['App', __NAMESPACE__];
    const APP = 'app';
    const FILEHANDLE_CONFIG = ':config';
    const FILEHANDLE_PARAMETERS = ':parameters';
    const FILEHANDLE_HACKS = ':hacks';

    protected static $defaultRuntime;
    protected static $args;

    // TODO: Document the diff here
    protected $arguments = [];
    protected $controllerArguments = [];

    // App objects
    protected $runtime;
    protected $logger;
    protected $router;
    protected $request;
    protected $response;
    protected $apis;
    protected $routing;
    protected $route;
    protected $cache;
    protected $db;
    protected $handler;

    protected $objects = ['runtime', 'config', 'logger', 'router', 'request', 'response', 'apis', 'routing', 'route', 'cache', 'db', 'handler'];

    public function __construct(AppParameters $parameters = null)
    {
        // Attach the Routes instance (collection of routes handled by the App
        $this->routes = new Routes();
        if (static::hasStaticProp('defaultRuntime')) {
            $this->setRuntime(static::getDefaultRuntime());
        }
        // Checkout if no app/build exists
        if (stream_resolve_include_path($this->getBuildFilePath()) == false) {
            $this->checkout();
        }
        Load::php(static::FILEHANDLE_HACKS);
        if (null == $parameters) {
            try {
                $arrayFile = new ArrayFile(static::FILEHANDLE_PARAMETERS);
            } catch (Exception $e) {
                throw new CoreException($e);
            }
            $parameters = new AppParameters($arrayFile->toArray());
        }
        try {
            if ($configFiles = $parameters->getDataKey(AppParameters::CONFIG_FILES)) {
                if ($this->hasObject('runtime')) {
                    $this->getRuntime()->runConfig(
                        (new RuntimeConfig())
                            ->processFromFiles($configFiles)
                    );
                }
            }
            // App handles cache
            if ($apis = $parameters->getDataKey(AppParameters::APIS)) {
                $this->setApis(
                    (new Apis())
                        ->registerArray($apis)
                );
            }
            // App handles cache
            if ($routes = $parameters->getDatakey(AppParameters::ROUTES)) {
                $this->setRouter(
                    (new Router())
                        ->prepareArray($routes)
                );
            }
        } catch (Exception $e) {
            throw new CoreException($e);
        }
        // Must get rid of the Routes instance
        Routes::destroyInstance();
        if (Console::bind($this)) {
            Console::run(); //Console::run() always exit.
        } else {
            $this->setRequest(Request::createFromGlobals());
        }
    }

    // TODO: Make trait
    public static function hasStaticProp(string $key): bool
    {
        return isset(static::$$key);
    }

    protected function setRuntime(Runtime $runtime): self
    {
        $this->runtime = $runtime;

        return $this;
    }

    public function getRuntime(): Runtime
    {
        return $this->runtime;
    }

    // protected function setRuntimeConfig(RuntimeConfig $config): self
    // {
    //     $this->runtimeConfig = $config;

    //     return $this;
    // }

    // public function getRuntimeConfig(): RuntimeConfig
    // {
    //     return $this->runtimeConfig;
    // }

    /**
     * Applies the RuntimeConfig.
     */
    // protected function configure(): self
    // {
    //     if (false == $this->hasObject('runtimeConfig')) {
    //         throw new CoreException(
    //             (new Message('Unable to apply runtimeConfig (no %s has been set).'))
    //                 ->code('%s', RuntimeConfig::class)
    //         );
    //     }
    //     $this->getRuntime()->runConfig($this->getRuntimeConfig());

    //     return $this;
    // }

    /**
     * Get the value of handler.
     */
    public function getHandler(): Handler
    {
        return $this->handler;
    }

    /**
     * Set the value of handler.
     *
     * @return self
     */
    protected function setHandler(Handler $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    protected function setRoute(Route $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    protected function setRouting(Routing $routing): self
    {
        $this->routing = $routing;

        return $this;
    }

    public function getRouting(): Routing
    {
        return $this->routing;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    // FIXME: Must be protected
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public static function getBuildFilePath(): string
    {
        return ROOT_PATH.App\PATH.'build';
    }

    protected function setApis(Apis $apis): self
    {
        $this->apis = $apis;

        return $this;
    }

    public function getApis(): Apis
    {
        return $this->apis;
    }

    public function getApi(string $key = null): ?array
    {
        return $this->apis->get($key ?? 'api');
    }

    /**
     * Get build time.
     */
    public function getBuildTime(): ?string
    {
        $filename = $this->getBuildFilePath();

        return File::exists($filename) ? (string) file_get_contents($filename) : null;
    }

    public function checkout(): void
    {
        $filename = $this->getBuildFilePath();
        $fh = @fopen($filename, 'w');
        if (!$fh) {
            throw new Exception(
                (new Message('Unable to open %f for writing'))->code('%f', $filename)
            );
        }
        if (@fwrite($fh, (string) time()) == false) {
            throw new Exception(
                (new Message('Unable to write to %f'))->code('%f', $filename)
            );
        }
        @fclose($fh);
    }

    /**
     * Run the callable and dispatch the handler.
     *
     * @param string $callable controller (path or class name)
     */
    public function run(string $callable = null)
    {
        // TODO: Run should detect if the app misses things needed for running.
        if (null == $callable) {
            try {
                $callable = $this->getRouting()->getController($this->getRequest());
                $this->setRoute($this->getRouting()->getRoute());
            } catch (RouterException $e) {
                die('APP RUN RESPONSE: '.$e->getCode());
            }
        }
        if (null != $callable) {
            $controller = $this->getControllerObject($callable);
            if ($controller instanceof Interfaces\RenderableInterface) {
                echo $controller->render();
            } else {
                // dd($this->getResponse()->val, $controller->getResponse()->val);
                $controller->getResponse()->sendJson();
            }
        }
    }

    /**
     * Runs a explicit provided callable.
     */
    public function getControllerObject(string $callable)
    {
        $this->setResponse(new Response());
        $controller = $this->getCallable($callable);
        if ($controller instanceof Controller) {
            $controller->setApp($this);
        }
        // HTTP request middleware
        // TODO: Re-Check
        if ($this->route instanceof Route && $middlewares = $this->route->getMiddlewares()) {
            $handler = new Handler($middlewares);
            $handler->runner($this);
        }
        // Use arguments taken from wildcards
        if ($this->arguments == null && $routingArgs = $this->getRouting()->getArguments()) {
            $this->setArguments($routingArgs);
        }
        if (is_object($controller)) {
            $method = '__invoke';
        } else {
            if (Utils\Str::contains('::', $controller)) {
                $controllerExplode = explode('::', $controller);
                $controller = $controllerExplode[0];
                $method = $controllerExplode[1];
            }
        }
        if (isset($method)) {
            $invoke = new ReflectionMethod($controller, $method);
        } else {
            // FIXME: php app/console run Chevereto\Core\Path::fromHandle
            dd(is_callable($controller));
            $invoke = new ReflectionFunction($controller);
        }
        $controllerArguments = [];
        $parameterIndex = 0;
        // Magically create typehinted objects
        foreach ($invoke->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            $type = $parameterType != null ? $parameterType->getName() : null;
            $value = $this->arguments[$parameter->getName()] ?? $this->arguments[$parameterIndex] ?? null;
            if ($type == null || in_array($type, Controller::TYPE_DECLARATIONS)) {
                $controllerArguments[] = $value ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
            } else {
                // Object typehint
                if ($value === null && $parameter->allowsNull()) {
                    $controllerArguments[] = null;
                } else {
                    $hasConstruct = method_exists($type, '__construct');
                    if ($hasConstruct == false) {
                        throw new Exception(
                            (new Message("Class %s doesn't have a constructor. %n %o typehinted in %f invoke function."))
                                ->code('%s', $type)
                                ->code('%o', $type.' $'.$parameter->getName().($parameter->isDefaultValueAvailable() ? ' = '.$parameter->getDefaultValue() : null))
                                ->code('%n', '#'.$parameter->getPosition())
                                ->code('%f', $controller)
                        );
                    }
                    $controllerArguments[] = new $type($value);
                }
            }
            ++$parameterIndex;
        }
        $this->controllerArguments = $controllerArguments;
        $controller(...$this->controllerArguments);

        return $controller;
    }

    /**
     * Farewell kids, my planet needs me.
     */
    public function terminate(string $message = null)
    {
        if ($message) {
            Console::log($message);
        }
        exit();
    }

    // FIXME: Must be protected
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    protected function setRouter(Router $router): self
    {
        if (false == $router->isProcessDone()) {
            $router->processRoutes();
        }
        $this->router = $router;
        $this->routing = new Routing(Routes::instance());

        return $this;
    }

    // FIXME: Must be protected?
    public function setArguments(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Forges a request (if no Request has been set).
     */
    public function forgeRequest(Request $request): self
    {
        if ($this->hasObject('request')) {
            throw new CoreException('Unable to forge request when the request has been already set.');
        }
        $this->setRequest($request);

        return $this;
    }

    protected function setRequest(Request $request): self
    {
        $this->request = $request;
        $pathinfo = ltrim($this->request->getPathInfo(), '/');
        $this->request->attributes->set('requestArray', explode('/', $pathinfo));
        $host = $_SERVER['HTTP_HOST'] ?? null;
        // $this->define('HTTP_HOST', $host);
        // $this->define('URL', App\HTTP_SCHEME . '://' . $host . ROOT_PATH_RELATIVE);
        return $this;
    }

    public function getHash(): string
    {
        return ($this->getConstant('App\VERSION') ?: null).$this->getBuildTime();
    }

    public function getConstant(string $name, string $namespace = 'App'): ?string
    {
        $constant = "\\$namespace\\$name";

        return defined($constant) ? constant($constant) : null;
    }

    public static function setDefaultRuntime(Runtime $runtime): void
    {
        static::$defaultRuntime = $runtime;
    }

    public static function getDefaultRuntime(): Runtime
    {
        return static::$defaultRuntime;
    }
}
