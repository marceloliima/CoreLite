<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if (!is_scalar($value) || trim((string) $value) === '') {
            $this->errors[$field] = "{$label} é obrigatório.";
        }
        return $this;
    }

    public function email(string $field, string $value, string $label = 'E-mail'): self
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] = "{$label} inválido.";
        }
        return $this;
    }

    public function length(string $field, string $value, int $min, int $max, string $label): self
    {
        $length = mb_strlen($value, 'UTF-8');
        if ($value !== '' && ($length < $min || $length > $max)) {
            $this->errors[$field] = "{$label} deve ter entre {$min} e {$max} caracteres.";
        }
        return $this;
    }

    public function password(string $field, string $value): self
    {
        if ($value === '') {
            return $this;
        }

        $valid = mb_strlen($value, 'UTF-8') >= 12
            && preg_match('/[A-Z]/', $value) === 1
            && preg_match('/[a-z]/', $value) === 1
            && preg_match('/\d/', $value) === 1
            && preg_match('/[^A-Za-z0-9]/', $value) === 1;

        if (!$valid) {
            $this->errors[$field] = 'A senha deve ter ao menos 12 caracteres, com maiúscula, minúscula, número e símbolo.';
        }

        return $this;
    }

    public function in(string $field, string $value, array $allowed, string $label): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} inválido.";
        }
        return $this;
    }

    public function same(string $field, string $value, string $other, string $label): self
    {
        if (!hash_equals($other, $value)) {
            $this->errors[$field] = "{$label} não confere.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
