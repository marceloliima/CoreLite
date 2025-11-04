<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\UsuarioController;
use App\Controllers\AuthController;

// ---------------------------------------------------------------
// 🔸 CONFIGURAÇÕES GERAIS
// ---------------------------------------------------------------
date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('display_errors', '1'); // alterar para 0 em produção

// ---------------------------------------------------------------
// 🔸 SESSÃO SEGURA
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'name' => 'APPSESSID'
    ]);
}

// ---------------------------------------------------------------
// 🔸 BOOTSTRAP
// ---------------------------------------------------------------
require __DIR__ . '/../App/bootstrap.php';

// ---------------------------------------------------------------
// 🔸 INICIALIZAÇÃO DO ROUTER
// ---------------------------------------------------------------
$router = new Router();

// ---------------------------------------------------------------
// 🔸 ROTAS DE AUTENTICAÇÃO
// ---------------------------------------------------------------
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ---------------------------------------------------------------
// 🔸 ROTAS DE USUÁRIOS (CRUD)
// ---------------------------------------------------------------
$router->group(['prefix' => 'usuarios'], function(Router $router) {
    $router->get('/', [UsuarioController::class, 'index']);
    $router->get('/show/{id}', [UsuarioController::class, 'show']);
    $router->get('/create', [UsuarioController::class, 'create']);
    $router->post('/store', [UsuarioController::class, 'store']);
    $router->get('/edit/{id}', [UsuarioController::class, 'edit']);
    $router->post('/update/{id}', [UsuarioController::class, 'update']);
    $router->post('/delete/{id}', [UsuarioController::class, 'delete']);
});

// ---------------------------------------------------------------
// 🔸 ROTAS DA HOME
// ---------------------------------------------------------------
$router->get('/', [UsuarioController::class, 'index']);

// ---------------------------------------------------------------
// 🔸 DISPATCH
// ---------------------------------------------------------------
$uri = filter_var($_SERVER['REQUEST_URI'] ?? '/', FILTER_SANITIZE_URL);
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    $router->dispatch($uri, $method);
} catch (Throwable $e) {
    error_log('[ROUTER ERROR] ' . $e->getMessage());

    if (ini_get('display_errors')) {
        echo "<h2>Erro interno na aplicação</h2>";
        echo "<pre>{$e}</pre>";
    } else {
        http_response_code(500);
        echo 'Erro interno do servidor.';
    }
}
