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

namespace Chevere\Components\App\Exceptions;

use Exception;

/**
 * Exception thrown if the application attempts to be maked more than once.
 */
final class BuildAlreadyMakedException extends Exception
{ }
