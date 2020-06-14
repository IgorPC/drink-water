<?php

declare(strict_types=1);

include '../vendor/autoload.php';

use App\Controller\DrinkController;
use App\Controller\UserController;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$responseFactory = new ResponseFactory();

$strategy = new League\Route\Strategy\JsonStrategy($responseFactory);
$router   = (new League\Route\Router)->setStrategy($strategy);

//Returns all users
$router->get('/users/', [UserController::class, 'index']);

//Create a new user
$router->post('/users/', [UserController::class, 'create']);

//Shows a specific user
$router->get('/users/{id}', [UserController::class, 'show']);

//Log in an already registered user
$router->post('/login', [UserController::class, 'login']);

//Updates the data of an already registered and authenticated user
$router->put('/users/{id}', [UserController::class, 'update']);

//Removes an already registered and authenticated user
$router->delete('/users/{id}', [UserController::class, 'destroy']);

//Updates the drink counter and history
$router->post('/users/{id}/drink', [DrinkController::class, 'drink']);

//Shows the drink history of a registered user
$router->get('/users/{id}/drink/historic', [DrinkController::class, 'historic']);

//Shows the user who drank more that day
$router->get('/users/drink/rank', [DrinkController::class, 'rank']);

$response = $router->dispatch($request);
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
