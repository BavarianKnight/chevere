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

use Chevere\Components\Filesystem\File;
use Chevere\Components\Message\Message;
use Chevere\Components\Spec\Specs\GroupSpec;
use Chevere\Components\Spec\Specs\IndexSpec;
use Chevere\Components\Spec\Specs\RoutableSpec;
use Chevere\Components\Str\Str;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Filesystem\FilesystemException;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Router\RouterInterface;
use Chevere\Interfaces\Spec\SpecIndexInterface;
use Chevere\Interfaces\Spec\SpecInterface;
use Chevere\Interfaces\Spec\SpecMakerInterface;
use Chevere\Interfaces\Spec\SpecPathInterface;
use Ds\Map;
use Throwable;

final class SpecMaker implements SpecMakerInterface
{
    private SpecPathInterface $specPath;

    private DirInterface $dir;

    private RouterInterface $router;

    private SpecIndex $specIndex;

    private IndexSpec $indexSpec;

    private Map $files;

    public function __construct(
        SpecPathInterface $specPath,
        DirInterface $dir,
        RouterInterface $router
    ) {
        $this->specPath = $specPath;
        $this->dir = $dir;
        $this->assertDir();
        $this->router = $router;
        $this->assertRouter();
        $this->specIndex = new SpecIndex;
        $this->indexSpec = new IndexSpec($this->specPath);
        $this->files = new Map;
        $routes = $router->routables();
        $groups = [];
        foreach ($routes->getGenerator() as $routeName => $routable) {
            $groupName = $router->index()->getRouteGroup($routeName);
            if (!isset($groups[$groupName])) {
                $groups[$groupName] = new GroupSpec($specPath, $groupName);
            }
            $routableSpec = new RoutableSpec(
                $specPath->getChild($groupName),
                $routable
            );
            $this->makeJsonFile($routableSpec);
            /** @var GroupSpec $groupSpec */
            $groupSpec = $groups[$groupName];
            $groupSpec = $groupSpec->withAddedRoutableSpec($routableSpec);
            $groups[$groupName] = $groupSpec;
            $routeEndpointSpecs = $routableSpec->clonedRouteEndpointSpecs();
            foreach ($routeEndpointSpecs->getGenerator() as $routeEndpointSpec) {
                $this->specIndex = $this->specIndex->withAddedRoute(
                    $routeName,
                    $routeEndpointSpec
                );
                $this->makeJsonFile($routeEndpointSpec);
            }
        }
        foreach ($groups as $groupSpec) {
            $this->makeJsonFile($groupSpec);
            $this->indexSpec = $this->indexSpec->withAddedGroup($groupSpec);
        }
        $this->makeJsonFile($this->indexSpec);
    }

    public function specIndex(): SpecIndexInterface
    {
        return $this->specIndex;
    }

    public function files(): Map
    {
        return $this->files->copy();
    }

    /**
     * @codeCoverageIgnore
     */
    private function assertDir(): void
    {
        try {
            if (!$this->dir->exists()) {
                $this->dir->create(0777);
            }
            $this->dir->assertExists();
            if (!$this->dir->path()->isWritable()) {
                throw new Exception(
                    (new Message('Directory %pathName% is not writable'))
                        ->code('%pathName%', $this->dir->path()->absolute())
                );
            }
            $this->dir->removeContents();
        } catch (Throwable $e) {
            throw new FilesystemException(
                (new Message('Unable to use %dirname%'))
                    ->code('%dirname%', $this->dir->path()->absolute()),
                0,
                $e
            );
        }
    }

    private function assertRouter(): void
    {
        if (count($this->router->routables()) === 0) {
            throw new InvalidArgumentException(
                (new Message('Instance of %interfaceName% does not contain any routable.'))
                    ->code('%interfaceName%', RouterInterface::class)
            );
        }
    }

    private function makeJsonFile(SpecInterface $spec): void
    {
        $filePath = $this->getPathFor($spec->jsonPath());
        $this->files[$spec->jsonPath()] = $filePath;
        try {
            $file = new File($filePath);
            if ($file->exists()) {
                $file->remove(); // @codeCoverageIgnore
            }
            $file->create();
            $file->put($this->toJson($spec->toArray()));
        }
        // @codeCoverageIgnoreStart
        catch (Throwable $e) {
            throw new FilesystemException(
                (new Message('Unable to make file %filename%'))
                    ->code('%filename%', $filePath->absolute()),
                0,
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function getPathFor(string $jsonPath): PathInterface
    {
        try {
            $dirPath = $this->dir->path();
            $child = (new Str($jsonPath))
                ->withReplaceFirst($this->specPath->toString(), '')
                ->toString();
            $child = ltrim($child, '/');

            return $dirPath->getChild($child);
        }
        // @codeCoverageIgnoreStart
        catch (Throwable $e) {
            throw new FilesystemException(
                (new Message('Unable to retrieve path for %argument%'))
                    ->code('%argument%', $jsonPath),
                0,
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function toJson(array $array): string
    {
        return json_encode($array, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
    }
}
