<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Formatter;
use App\Core\FlashMessage;
use App\Controllers\AuthController;
use App\Models\UsuarioModel;

class UsuarioController extends Controller
{
    private AuthController $auth;

    public function __construct()
    {
        $this->auth = new AuthController();
    }

    public function index(): string
    {
        $usuarios = (new UsuarioModel())->all();
        return $this->view('usuarios/index', ['usuarios' => $usuarios]);
    }

    public function show(int $id): string
    {
        $usuario = (new UsuarioModel())->find($id);

        if (!$usuario) {
            FlashMessage::definir('erro', 'Usuário não encontrado.');
            header('Location: /usuarios');
            exit;
        }

        return $this->view('usuarios/show', ['usuario' => $usuario]);
    }

    public function create(): string
    {
        $csrfToken = Csrf::token('form_usuario_criar');

        return $this->view('usuarios/create', [
            'csrf_token' => $csrfToken
        ]);
    }

    public function store(): void
    {
        $this->requirePost();

        if (!Csrf::check('form_usuario_criar', $_POST['csrf_token'] ?? null)) {
            FlashMessage::definir('erro', 'Token CSRF inválido ou expirado.');
            header('Location: /usuarios/create');
            exit;
        }

        $this->auth->exigirLogin('Você precisa estar logado.');
        $this->auth->exigirFuncao('admin');

        $nome   = Formatter::nome(trim($_POST['nome'] ?? ''));
        $email  = strtolower(trim($_POST['email'] ?? ''));
        $senha  = trim($_POST['senha'] ?? '');
        $funcao = $_POST['funcao'] ?? 'usuario';
        $status = $_POST['status'] ?? 'ativo';

        if (!$nome || !$email || !$senha) {
            FlashMessage::definir('erro', 'Todos os campos são obrigatórios.');
            header('Location: /usuarios/create');
            exit;
        }

        $usuarioModel = new UsuarioModel();
        if ($usuarioModel->emailExists($email)) {
            FlashMessage::definir('erro', 'E-mail já cadastrado.');
            header('Location: /usuarios/create');
            exit;
        }

        $usuario = new UsuarioModel([
            'nome'       => htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
            'email'      => $email,
            'senha_hash' => $senha,
            'funcao'     => $funcao,
            'status'     => $status
        ]);

        if ($usuario->saveUser()) {
            FlashMessage::definir('sucesso', 'Usuário criado com sucesso!');
            header('Location: /usuarios');
            exit;
        }

        FlashMessage::definir('erro', 'Erro ao criar usuário.');
        header('Location: /usuarios/create');
        exit;
    }

    public function edit(int $id): string
    {
        $this->auth->exigirLogin('Você precisa estar logado.');
        $this->auth->exigirFuncao('admin');

        $usuario = (new UsuarioModel())->find($id);

        if (!$usuario) {
            FlashMessage::definir('erro', 'Usuário não encontrado.');
            header('Location: /usuarios');
            exit;
        }

        $csrfToken = Csrf::token('form_usuario_editar_' . $id);

        return $this->view('usuarios/edit', [
            'usuario'    => $usuario,
            'csrf_token' => $csrfToken
        ]);
    }

    public function update(int $id): void
    {
        $this->requirePost();

        if (!Csrf::check('form_usuario_editar_' . $id, $_POST['csrf_token'] ?? null)) {
            FlashMessage::definir('erro', 'Token CSRF inválido ou expirado.');
            header('Location: /usuarios/edit/' . $id);
            exit;
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($id);

        if (!$usuario) {
            FlashMessage::definir('erro', 'Usuário não encontrado.');
            header('Location: /usuarios');
            exit;
        }

        $nome   = Formatter::nome(trim($_POST['nome'] ?? ''));
        $email  = strtolower(trim($_POST['email'] ?? ''));
        $senha  = trim($_POST['senha'] ?? '');
        $funcao = $_POST['funcao'] ?? $usuario->funcao;
        $status = $_POST['status'] ?? $usuario->status;

        if (!$nome || !$email) {
            FlashMessage::definir('erro', 'Campos obrigatórios ausentes.');
            header('Location: /usuarios/edit/' . $id);
            exit;
        }

        $usuario->nome  = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $usuario->email = $email;
        if (!empty($senha)) {
            $usuario->senha_hash = $senha;
        }
        $usuario->funcao = $funcao;
        $usuario->status = $status;

        if ($usuario->saveUser()) {
            FlashMessage::definir('sucesso', 'Usuário atualizado com sucesso!');
            header('Location: /usuarios/show/' . $usuario->id);
            exit;
        }

        FlashMessage::definir('erro', 'Erro ao atualizar usuário.');
        header('Location: /usuarios/edit/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        $this->requirePost();

        if (!Csrf::check('form_usuario_deletar_' . $id, $_POST['csrf_token'] ?? null)) {
            FlashMessage::definir('erro', 'Token CSRF inválido ou expirado.');
            header('Location: /usuarios');
            exit;
        }

        $usuario = (new UsuarioModel())->find($id);

        if (!$usuario) {
            FlashMessage::definir('erro', 'Usuário não encontrado.');
            header('Location: /usuarios');
            exit;
        }

        if ($usuario->deleteUser()) {
            FlashMessage::definir('sucesso', 'Usuário removido com sucesso!');
            header('Location: /usuarios');
            exit;
        }

        FlashMessage::definir('erro', 'Erro ao remover usuário.');
        header('Location: /usuarios');
        exit;
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            FlashMessage::definir('erro', 'Método HTTP não permitido.');
            exit;
        }
    }
}
