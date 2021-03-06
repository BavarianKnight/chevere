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

namespace Chevere\Components\Filesystem;

use Chevere\Components\Filesystem\Dir;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Message\Message;
use Chevere\Components\Str\StrBool;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Filesystem\FileExistsException;
use Chevere\Exceptions\Filesystem\FileNotExistsException;
use Chevere\Exceptions\Filesystem\FileUnableToCreateException;
use Chevere\Exceptions\Filesystem\FileUnableToGetException;
use Chevere\Exceptions\Filesystem\FileUnableToPutException;
use Chevere\Exceptions\Filesystem\FileUnableToRemoveException;
use Chevere\Exceptions\Filesystem\PathIsDirException;
use Chevere\Interfaces\Filesystem\FileInterface;
use Chevere\Interfaces\Filesystem\PathInterface;
use Throwable;

final class File implements FileInterface
{
    private PathInterface $path;

    private bool $isPhp;

    public function __construct(PathInterface $path)
    {
        $this->path = $path;
        $this->isPhp = (new StrBool($this->path->absolute()))->endsWith('.php');
        $this->assertIsNotDir();
    }

    public function path(): PathInterface
    {
        return $this->path;
    }

    public function isPhp(): bool
    {
        return $this->isPhp;
    }

    public function exists(): bool
    {
        return $this->path->exists() && $this->path->isFile();
    }

    public function assertExists(): void
    {
        if (!$this->exists()) {
            throw new FileNotExistsException(
                (new Message("File %path% doesn't exists"))
                    ->code('%path%', $this->path->absolute())
            );
        }
    }

    public function checksum(): string
    {
        $this->assertExists();

        return hash_file(FileInterface::CHECKSUM_ALGO, $this->path->absolute());
    }

    /**
     * @codeCoverageIgnoreStart
     * @throws FileNotExistsException
     * @throws FileUnableToGetException
     */
    public function contents(): string
    {
        $this->assertExists();
        try {
            $contents = file_get_contents($this->path->absolute());
            if (false === $contents) {
                throw new Exception(
                    (new Message('Failure in function %functionName%'))
                        ->code('%functionName%', 'file_get_contents')
                );
            }
        } catch (Throwable $e) {
            throw new FileUnableToGetException(
                (new Message('Unable to read the contents of the file at %path%'))
                    ->code('%path%', $this->path->absolute())
            );
        }

        return $contents;
    }

    public function remove(): void
    {
        $this->assertExists();
        // @codeCoverageIgnoreStart
        try {
            unlink($this->path->absolute());
        } catch (Throwable $e) {
            throw new FileUnableToRemoveException(
                (new Message('Unable to remove file %path%'))
                    ->code('%path%', $this->path->absolute())
            );
        }
        // @codeCoverageIgnoreEnd
    }

    public function create(): void
    {
        $this->assertIsNotDir();
        if ($this->path->exists()) {
            throw new FileExistsException(
                (new Message('Unable to create file %path% (file already exists)'))
                    ->code('%path%', $this->path->absolute())
            );
        }
        $this->createPath();
        if (false === file_put_contents($this->path->absolute(), null)) {
            // @codeCoverageIgnoreStart
            throw new FileUnableToCreateException(
                (new Message('Unable to create file %path% (file system error)'))
                    ->code('%path%', $this->path->absolute())
            );
            // @codeCoverageIgnoreEnd
        }
    }

    public function put(string $contents): void
    {
        $this->assertExists();
        if (false === file_put_contents($this->path->absolute(), $contents)) {
            // @codeCoverageIgnoreStart
            throw new FileUnableToPutException(
                (new Message('Unable to write content to file %filepath%'))
                    ->code('%filepath%', $this->path->absolute())
            );
            // @codeCoverageIgnoreEnd
        }
    }

    private function createPath(): void
    {
        $dirname = dirname($this->path->absolute());
        $path = new Path($dirname . '/');
        if (!$path->exists()) {
            (new Dir($path))->create();
        }
    }

    private function assertIsNotDir(): void
    {
        if ($this->path->isDir()) {
            throw new PathIsDirException(
                (new Message('Path %path% is a directory'))
                    ->code('%path%', $this->path->absolute())
            );
        }
    }
}
