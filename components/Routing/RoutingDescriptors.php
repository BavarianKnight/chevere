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

namespace Chevere\Components\Routing;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Core\OverflowException;
use Chevere\Interfaces\Routing\RoutingDescriptorInterface;
use Chevere\Interfaces\Routing\RoutingDescriptorsInterface;
use Ds\Set;
use Generator;
use Throwable;

final class RoutingDescriptors implements RoutingDescriptorsInterface
{
    private Set $set;

    private array $routesPath = [];

    private array $routesName = [];

    private RoutingDescriptorInterface $descriptor;

    private int $pos = -1;

    public function __construct()
    {
        $this->set = new Set;
    }

    public function withAdded(RoutingDescriptorInterface $descriptor): RoutingDescriptorsInterface
    {
        if ($this->set->contains($descriptor)) {
            throw new OverflowException(
                (new Message('Instance of object %object% has been already added'))
                    ->code('%object%', get_class($descriptor) . '#' . spl_object_id($descriptor))
            );
        }
        $new = clone $this;
        $new->descriptor = $descriptor;
        $new->pos++;
        try {
            $new->assertPushPath($descriptor->path()->toString());
            $new->assertPushName($descriptor->decorator()->name()->toString());
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                (new Message('Routing conflict affecting previously declared %route%'))
                    ->code(
                        '%route%',
                        $this->get($e->getCode())->dir()->path()->absolute()
                    ),
                0,
                $e
            );
        }

        $new->set->add($descriptor);

        return $new;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getGenerator(): Generator
    {
        foreach ($this->set as $key => $value) {
            yield $key => $value;
        }
    }

    public function count(): int
    {
        return $this->set->count();
    }

    public function contains(RoutingDescriptorInterface $descriptor): bool
    {
        return $this->set->contains($descriptor);
    }

    public function get(int $position): RoutingDescriptorInterface
    {
        $return = $this->set->get($position);
        if ($return === null) {
            // @codeCoverageIgnoreStart
            throw new OutOfBoundsException(
                (new Message('Position %pos% does not exists'))
                    ->code('%pos%', (string) $position)
            );
            // @codeCoverageIgnoreEnd
        }

        return $return;
    }

    private function assertPushPath(string $path): void
    {
        $pos = $this->routesPath[$path] ?? null;
        if (isset($pos)) {
            throw new Exception(
                (new Message('Route path %path% has been already added'))
                    ->code('%path%', $path),
                $pos
            );
        }
        $this->routesPath[$path] = $this->pos;
    }

    private function assertPushName(string $name): void
    {
        $pos = $this->routesName[$name] ?? null;
        if (isset($pos)) {
            throw new Exception(
                (new Message('Route %name% has been already added'))
                    ->code('%name%', $name),
                $pos
            );
        }
        $this->routesName[$name] = $this->pos;
    }
}
