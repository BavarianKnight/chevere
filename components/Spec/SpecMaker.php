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

namespace Chevere\Components\Spec;

use Chevere\Components\Filesystem\Interfaces\Dir\DirInterface;
use Chevere\Components\Message\Message;
use Chevere\Components\Route\Interfaces\RoutePathInterface;
use Chevere\Components\Router\Interfaces\RouterInterface;
use Chevere\Components\Router\RouteableObjectsRead;
use Chevere\Components\Spec\Exceptions\SpecInvalidArgumentException;
use Chevere\Components\Spec\Interfaces\SpecIndexInterface;
use LogicException;

/**
 * Makes the application spec, which is a distributed json-fileset describing
 * the routing groups, routes and endpoints.
 */
final class SpecMaker
{
    private string $basePath;

    private DirInterface $dir;

    private RouterInterface $router;

    private SpecIndexInterface $specIndex;

    public function __construct(
        RoutePathInterface $path,
        DirInterface $dir,
        RouterInterface $router
    ) {
        $this->basePath = $path->toString() . '/';
        $this->dir = $dir;
        $this->assertDir();
        $this->router = $router;
        $this->assertRouter();
        $this->specIndex = new SpecIndex();
        // $routeIdentifiers = $router->index()->objects();
        // $routeIdentifiers->rewind();
        // while ($routeIdentifiers->valid()) {
        //     xdd($routeIdentifiers->current());
        //     $routeIdentifiers->next();
        // }
        $routeables = $router->routeables();
        $routeables->rewind();
        // $routeableSpec = new RouteableSpec();
        xdd($routeables->current()->route());
        while ($routeables->valid()) {
            $routeables->next();
        }
        // xdd($router->objects());
        // xdd($router->groups()->toArray());
    }

    public function specIndex(): SpecIndexInterface
    {
        return $this->specIndex;
    }

    private function assertDir(): void
    {
        if (!$this->dir->exists()) {
            $this->dir->create(0777);
        }
        if (!$this->dir->path()->isWriteable()) {
            throw new LogicException(
                (new Message('Directory %pathName% is not writeable'))
                    ->code('%pathName%', $this->dir->path()->absolute())
                    ->toString()
            );
        }
    }

    private function assertRouter(): void
    {
        $checks = [
            'groups' => $this->router->hasGroups(),
            'index' => $this->router->hasIndex(),
            'named' => $this->router->hasNamed(),
            'regex' => $this->router->hasRegex(),
        ];
        $missing = array_filter($checks, fn (bool $bool) => $bool === false);
        $keys = array_keys($missing);
        if (!empty($keys)) {
            throw new SpecInvalidArgumentException(
                (new Message('Instance of %interfaceName% missing %propertyName% property(s).'))
                    ->code('%interfaceName%', RouterInterface::class)
                    ->code('%propertyName%', implode(', ', $keys))
                    ->toString()
            );
        }
        if ($this->router->routeables()->count() == 0) {
            throw new SpecInvalidArgumentException(
                (new Message('Instance of %interfaceName% does not contain any routeable.'))
                    ->code('%interfaceName%', RouterInterface::class)
                    ->toString()
            );
        }
    }
}