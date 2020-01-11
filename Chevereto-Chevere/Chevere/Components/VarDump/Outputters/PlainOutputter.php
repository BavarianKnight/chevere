<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\VarDump\Outputters;

use Chevere\Components\VarDump\Contracts\OutputterContract;
use Chevere\Components\VarDump\Outputters\AbstractOutputter;

final class PlainOutputter extends AbstractOutputter
{
    public function prepare(): OutputterContract
    {
        return $this;
    }
}
