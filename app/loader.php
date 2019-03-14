<?php
namespace Chevereto\Core;

use Symfony\Component\HttpFoundation\Request;
use Chevereto\Core\Http\RequestHandler;

/**
 * App initialization
 */
$app = new App();

/**
 * Build the API.
 */
$apis = new Apis();
$apis
    ->register('api', 'apis/api')
    ->register('api-alt', 'apis/api-alt');

$app->setApis($apis); // App handles cache

/**
 * Build the explicit routing, API is already routed at this point.
 */
$router = new Router();
$router
    ->prepare('routes:dashboard')
    ->prepare('routes:web');

$app->setRouter($router);  // App handles cache

/**
 * Console binds if php_sapi_name = cli.
 * Console::run() always exit.
 */
if (Console::bind($app)) {
    Console::run();
} else {
    $request = Request::createFromGlobals();
    $app->setRequest($request);
}

$app->run();

// Hook::before('deleteUser@api/users:DELETE', function ($that) {
//     // $that->private = 'muahahahaha';
//     $that->source .= ' 1-HOOK-BEFORE-11 ';
// }, 11);

// Hook::before('deleteUser@api/users:DELETE', function ($that) {
//     $that->source .= ' 2-HOOK-BEFORE-11 ';
// }, 11);

// Hook::before('deleteUser@api/users:DELETE', function ($that) {
//     $that->source .= ' HOOK-BEFORE-PN ';
// });
// Hook::before('deleteUser@api/users:DELETE', function ($that) {
//     $that->source .= ' 2HOOK-BEFORE-PN ';
// });

// Hook::after('deleteUser@api/users:DELETE', function ($that) {
//     $that->source .= ' HOOK-AFTER-P5';
// }, 5);


// echo '<pre>' . Utils\Dump::out(Hook::getAll()) . '</pre>';

// dump(Hook::getAll());
