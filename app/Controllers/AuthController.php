<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Core\FlashMessage;
use App\Core\Controller;
use App\Core\Csrf;

class AuthController extends Controller
{
    private const SESSION_KEY = 'usuario_logado';
    private const EXPIRATION  = 7200; // 2 horas

    private ?UsuarioModel $usuario = null;

    public function __construct()
    {
        $this->iniciarSessao();
        $this->carregarUsuarioSessao();
    }

    private function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function carregarUsuarioSessao(): void
    {
        if (!empty($_SESSION[self::SESSION_KEY]['id'])) {
            $usuario = (new UsuarioModel())->find((int)$_SESSION[self::SESSION_KEY]['id']);
            if ($usuario && $this->sessaoValida()) {
                $this->usuario = $usuario;
                $_SESSION[self::SESSION_KEY]['ultimo_acesso'] = time();
            } else {
                $this->logout();
            }
        }
    }

    private function sessaoValida(): bool
    {
        if (empty($_SESSION[self::SESSION_KEY]['ultimo_acesso'])) {
            return false;
        }
        return (time() - $_SESSION[self::SESSION_KEY]['ultimo_acesso']) <= self::EXPIRATION;
    }

    public function login(): mixed
    {
        $this->iniciarSessao();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tokenPost = $_POST['csrf_token'] ?? '';
            if (!Csrf::check('login', $tokenPost)) {
                FlashMessage::definir('erro', 'Token CSRF inválido ou expirado.');
                header('Location: /login');
                exit;
            }

            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $senha = trim($_POST['senha'] ?? '');

            if (!$email || !$senha) {
                FlashMessage::definir('erro', 'E-mail e senha são obrigatórios.');
                header('Location: /login');
                exit;
            }

            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->autenticar($email, $senha);

            if ($usuario) {
                $_SESSION[self::SESSION_KEY] = [
                    'id'            => $usuario->id,
                    'nome'          => $usuario->nome,
                    'email'         => $usuario->email,
                    'funcao'        => $usuario->funcao,
                    'ultimo_acesso' => time()
                ];

                $this->usuario = $usuario;

                FlashMessage::definir('sucesso', 'Login efetuado com sucesso!');
                header('Location: /');
                exit;
            }

            FlashMessage::definir('erro', 'E-mail ou senha inválidos.');
            header('Location: /login');
            exit;
        }

        // GET: gera token CSRF atualizado
        $csrfToken = Csrf::token('login');

        return $this->view('auth/login', [
            'csrf_token' => $csrfToken
        ]);
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        $this->usuario = null;
        FlashMessage::definir('sucesso', 'Você saiu do sistema.');
        header('Location: /login');
        exit;
    }

    public function getUsuario(): ?UsuarioModel
    {
        return $this->usuario;
    }

    public function estaLogado(): bool
    {
        return $this->usuario instanceof UsuarioModel && $this->usuario->id > 0;
    }

    public function temFuncao(string $funcao): bool
    {
        if (!$this->estaLogado()) return false;
        return strtolower($this->usuario->funcao) === strtolower($funcao);
    }

    public function temAlgumaFuncao(array $funcoes): bool
    {
        if (!$this->estaLogado()) return false;
        return in_array(strtolower($this->usuario->funcao), array_map('strtolower', $funcoes), true);
    }

    public function exigirLogin(?string $mensagem = null): void
    {
        if (!$this->estaLogado()) {
            $msg = $mensagem ?? 'Você precisa estar logado para acessar esta página.';
            FlashMessage::definir('erro', $msg);
            header('Location: /login');
            exit;
        }
    }

    public function exigirFuncao(string $funcao, ?string $mensagem = null): void
    {
        if (!$this->temFuncao($funcao)) {
            $msg = $mensagem ?? "Acesso negado. Função '{$funcao}' requerida.";
            FlashMessage::definir('erro', $msg);
            header('Location: /');
            exit;
        }
    }

    public function exigirAlgumaFuncao(array $funcoes, ?string $mensagem = null): void
    {
        if (!$this->temAlgumaFuncao($funcoes)) {
            $msg = $mensagem ?? "Acesso negado. Funções permitidas: " . implode(', ', $funcoes);
            FlashMessage::definir('erro', $msg);
            header('Location: /');
            exit;
        }
    }

    public function getId(): ?int
    {
        return $this->usuario->id ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->usuario->email ?? null;
    }

    public function getFuncao(): ?string
    {
        return $this->usuario->funcao ?? null;
    }

    public function atualizarUltimoAcesso(): void
    {
        if ($this->estaLogado()) {
            $_SESSION[self::SESSION_KEY]['ultimo_acesso'] = time();
        }
    }
}
