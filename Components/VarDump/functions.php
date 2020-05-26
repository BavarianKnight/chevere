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

use Chevere\Components\VarDump\Formatters\ConsoleFormatter;
use Chevere\Components\VarDump\Outputters\ConsoleOutputter;
use Chevere\Components\VarDump\VarDump;
use Chevere\Interfaces\VarDump\VarDumpInterface;
use function Chevere\Components\Writers\writers;

// @codeCoverageIgnoreStart
function varDump(...$vars): VarDumpInterface
{
    return
        (new VarDump(
            writers()->out(),
            new ConsoleFormatter,
            new ConsoleOutputter
        ))
            ->withVars(...$vars)
            ->withShift(1);
}
if (function_exists('xd') === false) {
    /**
     * Dumps information about one or more variables to the output stream
     */
    function xd(...$vars)
    {
        varDump(...$vars)->stream();
    }
}
if (function_exists('xdd') === false) {
    /**
     * Dumps information about one or more variables to the output stream and die().
     */
    function xdd(...$vars)
    {
        varDump(...$vars)->stream();
        die(0);
    }
}
// @codeCoverageIgnoreEnd