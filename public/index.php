<?php

ini_set('display_errors', 1);
ini_set('display_startup_error', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

session_start();

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/..');
$dotenv->load();

use Aura\Router\RouterContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USERNAME'),
    'password'  => getenv('DB_PASSWORD'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();
$map->get('index', '/', [
    'controller' => 'App\Controllers\IndexController',
    'action'     => 'indexAction',
]);
$map->get('addJobs', '/jobs/add', [
    'controller' => 'App\Controllers\JobsController',
    'action'     => 'getAddJobAction',
    'auth'       => true,
]);
$map->post('saveJobs', '/jobs/add', [
    'controller' => 'App\Controllers\JobsController',
    'action'     => 'getAddJobAction',
    'auth'       => true,
]);
$map->get('addUsers', '/users/add', [
    'controller' => 'App\Controllers\UsersController',
    'action'     => 'getAddUserAction',
    'auth'       => true,
]);
$map->post('saveUsers', '/users/add', [
    'controller' => 'App\Controllers\UsersController',
    'action'     => 'getAddUserAction',
    'auth'       => true,
]);
$map->get('loginForm', '/login', [
    'controller' => 'App\Controllers\AuthController',
    'action'     => 'getLogin',
]);
$map->post('auth', '/auth', [
    'controller' => 'App\Controllers\AuthController',
    'action'     => 'postLogin',
]);
$map->get('logout', '/logout', [
    'controller' => 'App\Controllers\AuthController',
    'action'     => 'getLogout',
    'auth'       => true,
]);
$map->get('admin', '/admin', [
    'controller' => 'App\Controllers\AdminController',
    'action'     => 'getIndex',
    'auth'       => true,
]);

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if ( ! $route) {
    $response = new HtmlResponse('Route Not Found!!', 404);
} else {
    $handlerData = $route->handler;
    $controllerName = $handlerData['controller'];
    $actionName = $handlerData['action'];
    $needsAuth = $handlerData['auth'] ?? false;

    $sessionUserId = $_SESSION['userId'] ?? null;
    if ($needsAuth && ! $sessionUserId) {
        $response = new RedirectResponse('/login');
    } else {
        $controller = new $controllerName;
        $response = $controller->$actionName($request);
    }
}

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
http_response_code($response->getStatusCode());

echo $response->getBody();
