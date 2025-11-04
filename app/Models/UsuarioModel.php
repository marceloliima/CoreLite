<?php

namespace App\Models;

use App\Core\Model;

/**
 * Class UsuarioModel
 *
 * Representa a tabela `usuarios` no banco de dados.
 * Todas as consultas retornam instâncias de UsuarioModel.
 *
 * @package App\Models
 */
class UsuarioModel extends Model
{
    // ======================================================
    // Propriedades correspondentes à tabela
    // ======================================================
    public int $id = 0;
    public string $nome = '';
    public string $email = '';
    public string $senha_hash = '';
    public string $funcao = 'usuario';
    public string $status = 'ativo';
    public string $criado_em;
    public ?string $atualizado_em = null;

    // Nome da tabela e chave primária
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id';

    // ======================================================
    // Construtor
    // ======================================================
    public function __construct(array $data = [])
    {
        parent::__construct();

        $this->id            = (int)($data['id'] ?? 0);
        $this->nome          = trim($data['nome'] ?? '');
        $this->email         = strtolower(trim($data['email'] ?? ''));
        $this->senha_hash    = $data['senha_hash'] ?? '';
        $this->funcao        = $data['funcao'] ?? 'usuario';
        $this->status        = $data['status'] ?? 'ativo';
        $this->criado_em     = $data['criado_em'] ?? date('Y-m-d H:i:s');
        $this->atualizado_em = $data['atualizado_em'] ?? null;
    }

    // ======================================================
    // Métodos específicos de Usuário
    // ======================================================

    /**
     * Verifica se o e-mail já existe
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return $this->where('email', '=', strtolower(trim($email)))->first() !== null;
    }

    /**
     * Salva (cria ou atualiza) o usuário
     *
     * @return bool
     */
    public function saveUser(array $data = [], ?int $id = null): bool
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        $data = [
            'nome'       => $this->nome,
            'email'      => $this->email,
            'senha_hash' => $this->senha_hash,
            'funcao'     => $this->funcao,
            'status'     => $this->status,
        ];

        // Garante hash bcrypt
        if (!str_starts_with($this->senha_hash, '$2y$')) {
            $data['senha_hash'] = $this->hashPassword($this->senha_hash);
        }

        if ($id || $this->id > 0) {
            $data['atualizado_em'] = date('Y-m-d H:i:s');
            $this->where($this->primaryKey, '=', $id ?? $this->id);
            return $this->update($data);
        }

        $newUser = $this->create($data);
        if ($newUser) {
            $this->id = $newUser->id;
            return true;
        }

        return false;
    }

    /**
     * Remove o usuário
     *
     * @return bool
     */
    public function deleteUser(): bool
    {
        if ($this->id <= 0) return false;
        return $this->deleteById($this->id);
    }

    /**
     * Autentica pelo e-mail e senha
     *
     * @param string $email
     * @param string $senha
     * @return UsuarioModel|null
     */
    public function autenticar(string $email, string $senha): ?self
    {
        $user = $this->where('email', '=', strtolower(trim($email)))
                     ->where('status', '=', 'ativo')
                     ->first();

        if ($user && $this->verifyPassword($senha, $user->senha_hash)) {
            return $user;
        }

        return null;
    }

    /**
     * Atualiza a senha do usuário
     */
    public function atualizarSenha(int $id, string $novaSenha): bool
    {
        return $this->where($this->primaryKey, '=', $id)
                    ->update([
                        'senha_hash'   => $this->hashPassword($novaSenha),
                        'atualizado_em'=> date('Y-m-d H:i:s'),
                    ]);
    }

    /**
     * Altera o status do usuário
     */
    public function alterarStatus(int $id, string $novoStatus): bool
    {
        return $this->where($this->primaryKey, '=', $id)
                    ->update([
                        'status'       => $novoStatus,
                        'atualizado_em'=> date('Y-m-d H:i:s'),
                    ]);
    }

    /**
     * Converte o model em array
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'nome'        => $this->nome,
            'email'       => $this->email,
            'senha_hash'  => $this->senha_hash,
            'funcao'      => $this->funcao,
            'status'      => $this->status,
            'criado_em'   => $this->criado_em,
            'atualizado_em' => $this->atualizado_em,
        ];
    }

    /**
     * Gera hash seguro de senha
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifica senha
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
