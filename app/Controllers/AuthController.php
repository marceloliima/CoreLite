<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Installation;
use App\Core\RateLimiter;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use PDOException;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (!Installation::isInstalled()) {
            redirect('/setup');
        }

        return $this->view('auth/login', [
            'csrfToken' => Csrf::token('login'),
            'registrationEnabled' => (bool) config('app.public_registration', true),
        ]);
    }

    public function login(): never
    {
        if (!Installation::isInstalled()) {
            redirect('/setup');
        }

        $this->verifyCsrf('login');

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $validator = (new Validator())
            ->required('email', $email, 'E-mail')
            ->email('email', $email)
            ->required('password', $password, 'Senha');

        if ($validator->fails()) {
            Session::flashOldInput(['email' => $email]);
            Flash::add('error', implode(' ', $validator->errors()));
            redirect('/login');
        }

        if (RateLimiter::tooManyLoginAttempts($email)) {
            Flash::add('error', 'Muitas tentativas recentes. Aguarde alguns minutos e tente novamente.');
            redirect('/login');
        }

        $success = Auth::attempt($email, $password);
        RateLimiter::recordLoginAttempt($email, $success);

        if (!$success) {
            Audit::log('login_failed', 'user', null, [
                'email_hash' => hash('sha256', $email),
            ]);
            Session::flashOldInput(['email' => $email]);
            Flash::add('error', 'E-mail ou senha inválidos.');
            redirect('/login');
        }

        Session::clearOldInput();
        Audit::log('login_success', 'user', Auth::id());
        Flash::add('success', 'Bem-vindo ao SecurePanel.');
        redirect('/');
    }

    public function showRegister(): string
    {
        $this->ensureRegistrationAvailable();

        return $this->view('auth/register', [
            'csrfToken' => Csrf::token('register'),
        ]);
    }

    public function register(): never
    {
        $this->ensureRegistrationAvailable();
        $this->verifyCsrf('register');

        if (RateLimiter::tooManyRegistrations()) {
            Flash::add('error', 'Muitas tentativas de cadastro a partir deste endereço. Tente novamente mais tarde.');
            redirect('/register');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = (new Validator())
            ->required('name', $name, 'Nome')
            ->length('name', $name, 2, 120, 'Nome')
            ->required('email', $email, 'E-mail')
            ->email('email', $email)
            ->length('email', $email, 5, 190, 'E-mail')
            ->required('password', $password, 'Senha')
            ->password('password', $password)
            ->same('password_confirmation', $confirmation, $password, 'Confirmação da senha');

        if ($validator->fails()) {
            RateLimiter::recordRegistrationAttempt($email, false);
            Session::flashOldInput(['name' => $name, 'email' => $email]);
            Flash::add('error', implode(' ', $validator->errors()));
            redirect('/register');
        }

        $model = new User();
        if ($model->emailExists($email)) {
            RateLimiter::recordRegistrationAttempt($email, false);
            Session::flashOldInput(['name' => $name]);
            // Mensagem genérica reduz enumeração de contas existentes.
            Flash::add('error', 'Não foi possível concluir o cadastro com os dados informados.');
            redirect('/register');
        }

        try {
            // role/status são definidos pelo servidor; nunca vêm do formulário público.
            $userId = $model->create([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'active',
            ]);
        } catch (PDOException) {
            RateLimiter::recordRegistrationAttempt($email, false);
            Flash::add('error', 'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.');
            redirect('/register');
        }

        RateLimiter::recordRegistrationAttempt($email, true);
        Session::clearOldInput();
        Audit::log('public_registration', 'user', $userId);
        Flash::add('success', 'Conta criada com sucesso. Agora você pode entrar.');
        redirect('/login');
    }

    public function showSetup(): string
    {
        if (Installation::isInstalled()) {
            redirect('/login');
        }

        return $this->view('auth/setup', [
            'csrfToken' => Csrf::token('setup'),
            'requiresInstallKey' => trim((string) config('app.install_key', '')) !== '',
        ]);
    }

    public function setup(): never
    {
        if (Installation::isInstalled()) {
            Response::abort(403, 'A instalação inicial já foi concluída.');
        }

        $this->verifyCsrf('setup');
        $this->validateInstallationKey();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $validator = (new Validator())
            ->required('name', $name, 'Nome')
            ->length('name', $name, 2, 120, 'Nome')
            ->required('email', $email, 'E-mail')
            ->email('email', $email)
            ->length('email', $email, 5, 190, 'E-mail')
            ->required('password', $password, 'Senha')
            ->password('password', $password)
            ->same('password_confirmation', $confirmation, $password, 'Confirmação da senha');

        if ($validator->fails()) {
            Session::flashOldInput(['name' => $name, 'email' => $email]);
            Flash::add('error', implode(' ', $validator->errors()));
            redirect('/setup');
        }

        try {
            $userId = Installation::createFirstAdmin($name, $email, $password);
        } catch (\Throwable $e) {
            $message = config('app.debug', false)
                ? $e->getMessage()
                : 'Não foi possível concluir a instalação inicial.';
            Flash::add('error', $message);
            redirect('/setup');
        }

        Session::clearOldInput();
        Audit::log('initial_admin_created', 'user', $userId);
        Flash::add('success', 'Administrador inicial criado. Faça login para continuar.');
        redirect('/login');
    }

    public function logout(): never
    {
        $this->verifyCsrf('logout');
        $userId = Auth::id();

        if ($userId !== null) {
            Audit::log('logout', 'user', $userId);
        }

        Auth::logout();
        Session::start();
        Flash::add('success', 'Sessão encerrada com segurança.');
        redirect('/login');
    }

    private function ensureRegistrationAvailable(): void
    {
        if (!Installation::isInstalled()) {
            redirect('/setup');
        }

        if (!(bool) config('app.public_registration', true)) {
            Response::abort(404, 'Cadastro público indisponível.');
        }
    }

    private function validateInstallationKey(): void
    {
        $expected = trim((string) config('app.install_key', ''));
        if ($expected === '') {
            return;
        }

        $provided = (string) ($_POST['installation_key'] ?? '');
        if ($provided === '' || !hash_equals($expected, $provided)) {
            Flash::add('error', 'Chave de instalação inválida.');
            redirect('/setup');
        }
    }
}
