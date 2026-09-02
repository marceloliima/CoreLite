<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use PDOException;

final class UserController extends Controller
{
    public function index(): string
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $role = (string) ($_GET['role'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = (new User())->paginate($search, $role, $status, $page);

        return $this->view('users/index', compact('result', 'search', 'role', 'status'));
    }

    public function show(string $id): string
    {
        $user = $this->findOr404((int) $id);
        return $this->view('users/show', ['user' => $user]);
    }

    public function create(): string
    {
        return $this->view('users/create', [
            'csrfToken' => Csrf::token('users.create'),
        ]);
    }

    public function store(): never
    {
        $this->verifyCsrf('users.create');
        $data = $this->validatedInput();
        $model = new User();

        if ($model->emailExists($data['email'])) {
            Session::flashOldInput($_POST);
            Flash::add('error', 'Já existe um usuário com esse e-mail.');
            redirect('/users/create');
        }

        try {
            $id = $model->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'],
                'status' => $data['status'],
            ]);
        } catch (PDOException) {
            Session::flashOldInput($_POST);
            Flash::add('error', 'Não foi possível criar o usuário. Confira os dados.');
            redirect('/users/create');
        }

        Session::clearOldInput();
        Audit::log('user_created', 'user', $id, [
            'role' => $data['role'],
            'status' => $data['status'],
        ]);
        Flash::add('success', 'Usuário criado com sucesso.');
        redirect('/users/' . $id);
    }

    public function edit(string $id): string
    {
        $user = $this->findOr404((int) $id);

        return $this->view('users/edit', [
            'user' => $user,
            'csrfToken' => Csrf::token('users.update.' . $id),
        ]);
    }

    public function update(string $id): never
    {
        $userId = (int) $id;
        $this->verifyCsrf('users.update.' . $id);

        $user = $this->findOr404($userId);
        $data = $this->validatedInput(true, $userId);
        $model = new User();

        if ($model->emailExists($data['email'], $userId)) {
            Session::flashOldInput($_POST);
            Flash::add('error', 'Já existe outro usuário com esse e-mail.');
            redirect('/users/' . $id . '/edit');
        }

        // Evita que o administrador atual se tranque para fora do sistema.
        if (Auth::id() === $userId && $data['status'] !== 'active') {
            Flash::add('error', 'Você não pode desativar sua própria conta.');
            redirect('/users/' . $id . '/edit');
        }

        if (Auth::id() === $userId && $user['role'] === 'admin' && $data['role'] !== 'admin') {
            Flash::add('error', 'Você não pode remover sua própria função de administrador.');
            redirect('/users/' . $id . '/edit');
        }

        // Mantém no mínimo um administrador ativo no sistema.
        $wouldRemoveActiveAdmin = $user['role'] === 'admin'
            && $user['status'] === 'active'
            && ($data['role'] !== 'admin' || $data['status'] !== 'active');

        if ($wouldRemoveActiveAdmin && $model->countActiveAdmins() <= 1) {
            Flash::add('error', 'O sistema precisa manter pelo menos um administrador ativo.');
            redirect('/users/' . $id . '/edit');
        }

        try {
            $model->update($userId, [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'status' => $data['status'],
                'password_hash' => $data['password'] !== ''
                    ? password_hash($data['password'], PASSWORD_DEFAULT)
                    : null,
            ]);
        } catch (PDOException) {
            Session::flashOldInput($_POST);
            Flash::add('error', 'Não foi possível atualizar o usuário. Confira os dados.');
            redirect('/users/' . $id . '/edit');
        }

        Session::clearOldInput();
        Audit::log('user_updated', 'user', $userId, [
            'role' => $data['role'],
            'status' => $data['status'],
        ]);
        Flash::add('success', 'Usuário atualizado com sucesso.');
        redirect('/users/' . $id);
    }

    public function status(string $id): never
    {
        $userId = (int) $id;
        $this->verifyCsrf('users.status.' . $id);
        $user = $this->findOr404($userId);

        if (Auth::id() === $userId) {
            Flash::add('error', 'Você não pode alterar o status da própria conta por esta ação.');
            redirect('/users');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $model = new User();

        if ($user['role'] === 'admin' && $newStatus === 'inactive' && $model->countActiveAdmins() <= 1) {
            Flash::add('error', 'O último administrador ativo não pode ser desativado.');
            redirect('/users');
        }

        $model->updateStatus($userId, $newStatus);
        Audit::log('user_status_changed', 'user', $userId, ['status' => $newStatus]);
        Flash::add('success', 'Status atualizado.');
        redirect('/users');
    }

    public function delete(string $id): never
    {
        $userId = (int) $id;
        $this->verifyCsrf('users.delete.' . $id);
        $user = $this->findOr404($userId);

        if (Auth::id() === $userId) {
            Flash::add('error', 'Você não pode remover a própria conta.');
            redirect('/users');
        }

        $model = new User();
        if ($user['role'] === 'admin' && $user['status'] === 'active' && $model->countActiveAdmins() <= 1) {
            Flash::add('error', 'O último administrador ativo não pode ser removido.');
            redirect('/users');
        }

        $model->softDelete($userId);
        Audit::log('user_deleted', 'user', $userId);
        Flash::add('success', 'Usuário removido com segurança.');
        redirect('/users');
    }

    private function validatedInput(bool $passwordOptional = false, ?int $editingId = null): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $role = (string) ($_POST['role'] ?? 'user');
        $status = (string) ($_POST['status'] ?? 'active');

        $validator = (new Validator())
            ->required('name', $name, 'Nome')
            ->length('name', $name, 2, 120, 'Nome')
            ->required('email', $email, 'E-mail')
            ->email('email', $email)
            ->length('email', $email, 5, 190, 'E-mail')
            ->in('role', $role, ['admin', 'manager', 'user'], 'Perfil')
            ->in('status', $status, ['active', 'inactive'], 'Status');

        if (!$passwordOptional || $password !== '') {
            $validator
                ->required('password', $password, 'Senha')
                ->password('password', $password)
                ->same('password_confirmation', $confirmation, $password, 'Confirmação da senha');
        }

        if ($validator->fails()) {
            Session::flashOldInput($_POST);
            Flash::add('error', implode(' ', $validator->errors()));
            redirect($passwordOptional && $editingId !== null
                ? '/users/' . $editingId . '/edit'
                : '/users/create');
        }

        return compact('name', 'email', 'password', 'role', 'status');
    }

    private function findOr404(int $id): array
    {
        if ($id <= 0) {
            Response::abort(404);
        }

        $user = (new User())->find($id);
        if (!$user || $user['deleted_at'] !== null) {
            Response::abort(404, 'Usuário não encontrado.');
        }

        return $user;
    }
}
