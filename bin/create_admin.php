<?php

declare(strict_types=1);

use App\Core\Installation;
use App\Core\Validator;
use App\Models\User;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

function ask(string $label): string
{
    echo $label;
    return trim((string) fgets(STDIN));
}

$name = ask('Nome do administrador: ');
$email = strtolower(ask('E-mail: '));
$password = ask('Senha (mínimo 12 caracteres, maiúscula, minúscula, número e símbolo): ');
$confirmation = ask('Confirme a senha: ');

$validator = (new Validator())
    ->required('name', $name, 'Nome')
    ->length('name', $name, 2, 120, 'Nome')
    ->required('email', $email, 'E-mail')
    ->email('email', $email)
    ->required('password', $password, 'Senha')
    ->password('password', $password)
    ->same('password_confirmation', $confirmation, $password, 'Confirmação da senha');

if ($validator->fails()) {
    foreach ($validator->errors() as $error) {
        fwrite(STDERR, "- {$error}\n");
    }
    exit(1);
}

$model = new User();
if ($model->emailExists($email)) {
    fwrite(STDERR, "Esse e-mail já está cadastrado.\n");
    exit(1);
}

$id = $model->create([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'admin',
    'status' => 'active',
]);

Installation::markInstalledIfUsersExist();

echo "Administrador criado com sucesso. ID: {$id}\n";
