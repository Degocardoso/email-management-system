<?php

namespace App\Validators;

/**
 * Validador de Login
 *
 * Valida os dados de login do usuário
 */
class LoginValidator extends BaseValidator
{
    /**
     * Valida dados de login
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        $this->clearErrors();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Valida email
        if (!$this->required('email', $email, 'O email é obrigatório.')) {
            return false;
        }

        if (!$this->email('email', $email, 'O email informado é inválido.')) {
            return false;
        }

        // Valida senha
        if (!$this->required('password', $password, 'A senha é obrigatória.')) {
            return false;
        }

        return empty($this->errors);
    }
}
