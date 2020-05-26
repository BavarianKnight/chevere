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

namespace Chevere\Components\Plugs\Hooks;

use Chevere\Components\Message\Message;
use Chevere\Interfaces\Plugin\PlugsQueueInterface;
use Chevere\Interfaces\Plugs\Hooks\HookInterface;
use Chevere\Interfaces\Plugs\Hooks\HooksRunnerInterface;
use Chevere\Components\Type\Type;
use RuntimeException;
use Throwable;

/**
 * Queue handler for Hooks registered for a given HookableInterface.
 */
final class HooksRunner implements HooksRunnerInterface
{
    private PlugsQueueInterface $queue;

    private HookInterface $hook;

    public function __construct(HooksQueue $queue)
    {
        $this->queue = $queue->queue();
    }

    public function run(string $anchor, &$argument): void
    {
        if ($this->isLooping()) {
            return;
        }
        $queue = $this->queue->toArray()[$anchor] ?? null;
        if ($queue === null) {
            return;
        }
        $gettype = gettype($argument);
        if ($gettype === 'object') {
            $gettype = get_class($argument);
        }
        $type = new Type($gettype);
        foreach ($queue as $entries) {
            foreach ($entries as $entry) {
                try {
                    $this->hook = new $entry;
                } catch (Throwable $e) {
                    new RuntimeException(
                        (new Message('Invalid hook type'))
                            ->toString()
                    );
                }
                $hook = $this->hook;
                $hook($argument);
                if (!$type->validate($argument)) {
                    throw new RuntimeException(
                        (new Message('Hook argument %passed% has been altered to %altered% by hook %hook%'))
                                ->code('%passed%', $gettype)
                                ->code('%altered%', gettype($argument))
                                ->code('%hook%', get_class($entry))
                                ->toString()
                    );
                }
            }
        }
    }

    private function isLooping(): bool
    {
        return is_a(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4]['class'],
            HookInterface::class,
            true
        );
    }
}