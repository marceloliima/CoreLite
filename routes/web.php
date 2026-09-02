<?php

declare(strict_types=1);

use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;

$router->get('/setup', [AuthController::class, 'showSetup'], ['guest']);
$router->post('/setup', [AuthController::class, 'setup'], ['guest']);

$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

$router->get('/', [DashboardController::class, 'index'], ['auth']);
$router->get('/profile', [ProfileController::class, 'edit'], ['auth']);
$router->post('/profile', [ProfileController::class, 'update'], ['auth']);
$router->post('/profile/password', [ProfileController::class, 'password'], ['auth']);

$router->get('/users', [UserController::class, 'index'], ['auth', 'manager']);
$router->get('/users/create', [UserController::class, 'create'], ['auth', 'admin']);
$router->post('/users', [UserController::class, 'store'], ['auth', 'admin']);
$router->get('/users/{id}', [UserController::class, 'show'], ['auth', 'manager']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'admin']);
$router->post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'admin']);
$router->post('/users/{id}/status', [UserController::class, 'status'], ['auth', 'admin']);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'admin']);

$router->get('/audit', [AuditController::class, 'index'], ['auth', 'admin']);
