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

namespace Chevere\Components\App\Instances;

use Chevere\Components\App\Instances\Traits\AssertInstanceTrait;
use Chevere\Contracts\Http\RequestContract;

/**
 * A container for the global request instance.
 */
final class RequestInstance
{
    use AssertInstanceTrait;

    /** @var RequestContract */
    private static $instance;

    public function __construct(RequestContract $request)
    {
        self::set($request);
    }

    public static function set(RequestContract $request): void
    {
        self::$instance = $request;
    }

    public static function type(): string
    {
        return RequestContract::class;
    }

    public static function get(): RequestContract
    {
        self::assertInstance();
        
        return self::$instance;
    }
}
