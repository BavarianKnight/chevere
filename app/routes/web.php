<?php

namespace App;

use Chevere\Components\Route\Route;
use Chevere\Components\Http\Method;

return [
    (new Route('/home'))
        ->withAddedMethod(
            (new Method('GET'))
                ->withControllerName(Controllers\Home::class)
        )
        ->withName('homepageHtml'),
    (new Route('/'))
        ->withAddedMethod(
            (new Method('GET'))
                ->withControllerName(Controllers\Index::class)
        )
        ->withName('homepage'),
    // ->addMiddleware(Middlewares\RoleAdmin::class)
    // ->addMiddleware(Middlewares\RoleBanned::class),
    (new Route('/cache/{llave?}-{cert}-{user?}'))
        ->withWhere('llave', '[0-9]+')
        ->withAddedMethod(
            (new Method('GET'))
                ->withControllerName(Controllers\Cache::class)
        )
        ->withAddedMethod(
            (new Method('POST'))
                ->withControllerName(Controllers\Cache::class)
        )
        ->withName('cache'),
];
