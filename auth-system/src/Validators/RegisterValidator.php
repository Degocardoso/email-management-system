<?php

namespace App\Validators;

use App\Bootstrap;

/**
 * Validador de Registro
 *
 * Valida os dados de registro de novo usuário
 */
class RegisterValidator extends BaseValidator
{
    /**
     * Valida dados de registro
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        $this->clearErrors();

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';

        // Valida nome
        if (!$this->required('name', $name, 'O nome é obrigatório.')) {
            return false;
        }

        if (!$this->minLength('name', $name, 3, 'O nome deve ter no mínimo 3 caracteres.')) {
            return false;
        }

        if (!$this->maxLength('name', $name, 255, 'O nome deve ter no máximo 255 caracteres.')) {
            return false;
        }

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

        if (!$this->validatePasswordStrength($password)) {
            return false;
        }

        // Valida confirmação de senha
        if (!$this->required('password_confirm', $passwordConfirm, 'A confirmação de senha é obrigatória.')) {
            return false;
        }

        if (!$this->matches('password_confirm', $passwordConfirm, 'password', $password, 'As senhas não conferem.')) {
            return false;
        }

        return empty($this->errors);
    }

    /**
     * Valida força da senha conforme configurações
     *
     * @param string $password
     * @return bool
     */
    private function validatePasswordStrength(string $password): bool
    {
        $config = Bootstrap::getInstance()->getConfig('app.security');

        $minLength = $config['password_min_length'];
        $requireUppercase = $config['password_require_uppercase'];
        $requireLowercase = $config['password_require_lowercase'];
        $requireNumbers = $config['password_require_numbers'];
        $requireSpecial = $config['password_require_special'];

        // Valida tamanho mínimo
        if (strlen($password) < $minLength) {
            $this->addError('password', "A senha deve ter no mínimo {$minLength} caracteres.");
            return false;
        }

        // Valida letra maiúscula
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $this->addError('password', 'A senha deve conter pelo menos uma letra maiúscula.');
            return false;
        }

        // Valida letra minúscula
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            $this->addError('password', 'A senha deve conter pelo menos uma letra minúscula.');
            return false;
        }

        // Valida número
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $this->addError('password', 'A senha deve conter pelo menos um número.');
            return false;
        }

        // Valida caractere especial
        if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->addError('password', 'A senha deve conter pelo menos um caractere especial.');
            return false;
        }

        return true;
    }
}
