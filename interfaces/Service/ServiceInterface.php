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

namespace Chevere\Interfaces\Service;

/**
 * Describes the component in charge of defining a service.
 */
interface ServiceInterface
{
    /**
     * Returns the service description.
     */
    public function getDescription(): string;
}
