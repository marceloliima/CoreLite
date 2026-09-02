<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class ProfileController extends Controller
{
    public function edit(): string
    {
        return $this->view('profile/edit', [
            'csrfProfile' => Csrf::token('profile.update'),
            'csrfPassword' => Csrf::token('profile.password'),
        ]);
    }

    public function update(): never
    {
        $this->verifyCsrf('profile.update');
        $userId = Auth::id();
        if ($userId === null) {
            redirect('/login');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        $validator = (new Validator())
            ->required('name', $name, 'Nome')
            ->length('name', $name, 2, 120, 'Nome')
            ->required('email', $email, 'E-mail')
            ->email('email', $email)
            ->length('email', $email, 5, 190, 'E-mail');

        if ($validator->fails()) {
            Flash::add('error', implode(' ', $validator->errors()));
            redirect('/profile');
        }

        $model = new User();
        if ($model->emailExists($email, $userId)) {
            Flash::add('error', 'Esse e-mail já está sendo usado por outra conta.');
            redirect('/profile');
        }

        $model->updateOwnProfile($userId, $name, $email);
        Audit::log('profile_updated', 'user', $userId);
        Flash::add('success', 'Perfil atualizado com sucesso.');
        redirect('/profile');
    }

    public function password(): never
    {
        $this->verifyCsrf('profile.password');
        $userId = Auth::id();
        if ($userId === null) {
            redirect('/login');
        }

        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = (new Validator())
            ->required('current_password', $current, 'Senha atual')
            ->required('password', $password, 'Nova senha')
            ->password('password', $password)
            ->same('password_confirmation', $confirmation, $password, 'Confirmação da senha');

        if ($validator->fails()) {
            Flash::add('error', implode(' ', $validator->errors()));
            redirect('/profile');
        }

        $model = new User();
        $currentHash = $model->passwordHash($userId);

        if ($currentHash === null || !password_verify($current, $currentHash)) {
            Flash::add('error', 'A senha atual está incorreta.');
            redirect('/profile');
        }

        if (password_verify($password, $currentHash)) {
            Flash::add('error', 'A nova senha deve ser diferente da senha atual.');
            redirect('/profile');
        }

        $model->updatePasswordHash($userId, password_hash($password, PASSWORD_DEFAULT));
        Session::regenerate();
        Audit::log('password_changed', 'user', $userId);
        Flash::add('success', 'Senha alterada com segurança.');
        redirect('/profile');
    }
}
