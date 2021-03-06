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

namespace Chevere\Components\Plugin;

use Chevere\Components\ClassMap\ClassMap;
use Chevere\Components\Message\Message;
use Chevere\Components\Type\Type;
use Chevere\Exceptions\ClassMap\ClassNotMappedException;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Exceptions\Core\TypeException;
use Chevere\Exceptions\Plugin\PluggableNotRegisteredException;
use Chevere\Exceptions\Plugin\PlugsFileNotExistsException;
use Chevere\Exceptions\Plugin\PlugsQueueInterfaceException;
use Chevere\Interfaces\ClassMap\ClassMapInterface;
use Chevere\Interfaces\Plugin\PluginsInterface;
use Chevere\Interfaces\Plugin\PlugsQueueInterface;
use Throwable;
use TypeError;
use function Chevere\Components\Filesystem\filePhpReturnForString;
use function Chevere\Components\Filesystem\varForFilePhpReturn;
use function DeepCopy\deep_copy;

/**
 * Pluggable -> plugs.php interaction
 */
final class Plugins implements PluginsInterface
{
    private ClassMap $classMap;

    private string $plugsPath;

    public function __construct(ClassMapInterface $pluggablesToPlugs)
    {
        $this->classMap = $pluggablesToPlugs;
    }

    public function clonedClassMap(): ClassMapInterface
    {
        return deep_copy($this->classMap);
    }

    public function getPlugsQueue(string $pluggableName): PlugsQueueInterface
    {
        $this->assertSetPlugsPath($pluggableName);
        $this->assertPlugsPath();
        $fileReturn = filePhpReturnForString($this->plugsPath)
            ->withStrict(false);
        /**
         * @var PlugsQueueInterface $var
         */
        $var = varForFilePhpReturn($fileReturn, new Type(PlugsQueueInterface::class));

        return $var;
    }

    private function assertSetPlugsPath(string $pluggableName): void
    {
        try {
            $this->plugsPath = $this->classMap->get($pluggableName);
        } catch (ClassNotMappedException $e) {
            throw new PluggableNotRegisteredException($e->message());
        }
    }

    private function assertPlugsPath(): void
    {
        if (stream_resolve_include_path($this->plugsPath) === false) {
            throw new PlugsFileNotExistsException(
                (new Message("File %fileName% doesn't exists"))
                    ->code('%fileName%', $this->plugsPath)
            );
        }
    }
}
