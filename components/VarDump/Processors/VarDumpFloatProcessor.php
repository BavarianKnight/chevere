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

namespace Chevere\Components\VarDump\Processors;

use Chevere\Components\VarDump\Processors\Traits\ProcessorTrait;
use Chevere\Interfaces\Type\TypeInterface;
use Chevere\Interfaces\VarDump\VarDumperInterface;
use Chevere\Interfaces\VarDump\VarDumpProcessorInterface;

final class VarDumpFloatProcessor implements VarDumpProcessorInterface
{
    use ProcessorTrait;

    private string $stringVar = '';

    public function __construct(VarDumperInterface $varDumper)
    {
        $this->varDumper = $varDumper;
        $this->assertType();
        $this->stringVar = (string) $this->varDumper->dumpable()->var();
        $this->info = 'length=' . strlen($this->stringVar);
    }

    public function type(): string
    {
        return TypeInterface::FLOAT;
    }

    public function write(): void
    {
        $this->varDumper->writer()->write(
            implode(' ', [
                $this->typeHighlighted(),
                $this->varDumper->formatter()->filterEncodedChars($this->stringVar),
                $this->highlightParentheses($this->info)
            ])
        );
    }
}
