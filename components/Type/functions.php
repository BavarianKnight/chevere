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

namespace Chevere\Components\Type;

use Chevere\Components\Message\Message;
use Chevere\Interfaces\Message\MessageInterface;

function varType($var): string
{
    $type = strtolower(gettype($var));
    if ($type === 'double') {
        return 'float';
    }

    return $type;
}

function debugType($var): string
{
    $type = varType($var);
    if ($type === 'object') {
        return get_class($var);
    }

    return $type;
}

function returnTypeExceptionMessage(string $expected, string $provided): MessageInterface
{
    return (new Message('Expecting return type %expected%, type %provided% provided'))
        ->code('%expected%', $expected)
        ->code('%provided%', $provided);
}
