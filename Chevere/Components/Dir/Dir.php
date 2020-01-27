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

namespace Chevere\Components\Dir;

use Chevere\Components\Dir\Exceptions\DirUnableToCreateException;
use Chevere\Components\Dir\Exceptions\DirUnableToRemoveException;
use Chevere\Components\File\Exceptions\FileUnableToRemoveException;
use Chevere\Components\Dir\Exceptions\DirExistsException;
use Chevere\Components\Message\Message;
use Chevere\Components\Path\Exceptions\PathIsFileException;
use Chevere\Components\Path\Exceptions\PathIsNotDirectoryException;
use Chevere\Components\Dir\Interfaces\DirInterface;
use Chevere\Components\Path\Interfaces\PathInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * This class provides interactions for a directory in the application namespace.
 */
final class Dir implements DirInterface
{
    private PathInterface $path;

    /**
     * Creates a new instance.
     *
     * @throws PathIsFileException if the PathInterface represents a file
     */
    public function __construct(PathInterface $path)
    {
        $this->path = $path;
        $this->assertDirectory();
    }

    /**
     * {@inheritdoc}
     */
    public function path(): PathInterface
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return $this->path->exists() && $this->path->isDir();
    }

    /**
     * {@inheritdoc}
     */
    public function create(): void
    {
        if ($this->path->exists()) {
            throw new DirExistsException(
                (new Message('Directory %path% already exists'))
                    ->code('%path%', $this->path->absolute())
                    ->toString()
            );
        }
        if (!mkdir($this->path->absolute(), 0777, true)) {
            throw new DirUnableToCreateException(
                (new Message('Unable to create directory %path%'))
                    ->code('%path%', $this->path->absolute())
                    ->toString()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(): array
    {
        $this->assertIsDir();
        $array = $this->removeContents();
        if (!rmdir($this->path->absolute())) {
            throw new DirUnableToRemoveException(
                (new Message('Unable to remove directory %path%'))
                    ->code('%path%', $this->path->absolute())
                    ->toString()
            );
        }
        $array[] = $this->path->absolute();

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function removeContents(): array
    {
        $this->assertIsDir();
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path->absolute(),
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $removed = [];
        foreach ($files as $fileinfo) {
            $content = $fileinfo->getRealPath();
            if ($fileinfo->isDir()) {
                if (!rmdir($content)) {
                    throw new DirUnableToRemoveException(
                        (new Message('Unable to remove directory %path%'))
                            ->code('%path%', $this->path->absolute())
                            ->toString()
                    );
                }
                $removed[] = $content;
                continue;
            }
            if (!unlink($content)) {
                throw new FileUnableToRemoveException(
                    (new Message('Unable to remove file %path%'))
                        ->code('%path%', $this->path->absolute())
                        ->toString()
                );
            }
            $removed[] = $content;
        }

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function getChild(string $path): DirInterface
    {
        return new Dir(
            $this->path->getChild($path)
        );
    }

    private function assertDirectory(): void
    {
        if ($this->path->isFile()) {
            throw new PathIsFileException(
                (new Message('Path %path% is a file'))
                    ->code('%path%', $this->path->absolute())
                    ->toString()
            );
        }
    }

    private function assertIsDir(): void
    {
        if (!$this->path->isDir()) {
            throw new PathIsNotDirectoryException(
                (new Message('Path %path% is not a directory'))
                    ->code('%path%', $this->path->absolute())
                    ->toString()
            );
        }
    }
}