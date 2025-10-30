<?php

namespace App\Validators;

/**
 * Classe Base para Validadores
 *
 * Fornece métodos comuns de validação
 */
abstract class BaseValidator
{
    protected $errors = [];

    /**
     * Valida os dados
     *
     * @param array $data
     * @return bool
     */
    abstract public function validate(array $data): bool;

    /**
     * Retorna todos os erros de validação
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna o primeiro erro de validação
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Adiciona um erro de validação
     *
     * @param string $field
     * @param string $message
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Limpa os erros
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Valida campo obrigatório
     *
     * @param string $field
     * @param mixed $value
     * @param string $message
     * @return bool
     */
    protected function required(string $field, $value, string $message = null): bool
    {
        if (empty($value) && $value !== '0') {
            $this->addError($field, $message ?? "O campo {$field} é obrigatório.");
            return false;
        }
        return true;
    }

    /**
     * Valida email
     *
     * @param string $field
     * @param string $value
     * @param string $message
     * @return bool
     */
    protected function email(string $field, string $value, string $message = null): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? "O email informado é inválido.");
            return false;
        }
        return true;
    }

    /**
     * Valida tamanho mínimo
     *
     * @param string $field
     * @param string $value
     * @param int $min
     * @param string $message
     * @return bool
     */
    protected function minLength(string $field, string $value, int $min, string $message = null): bool
    {
        if (strlen($value) < $min) {
            $this->addError($field, $message ?? "O campo {$field} deve ter no mínimo {$min} caracteres.");
            return false;
        }
        return true;
    }

    /**
     * Valida tamanho máximo
     *
     * @param string $field
     * @param string $value
     * @param int $max
     * @param string $message
     * @return bool
     */
    protected function maxLength(string $field, string $value, int $max, string $message = null): bool
    {
        if (strlen($value) > $max) {
            $this->addError($field, $message ?? "O campo {$field} deve ter no máximo {$max} caracteres.");
            return false;
        }
        return true;
    }

    /**
     * Valida se os campos são iguais
     *
     * @param string $field1
     * @param mixed $value1
     * @param string $field2
     * @param mixed $value2
     * @param string $message
     * @return bool
     */
    protected function matches(string $field1, $value1, string $field2, $value2, string $message = null): bool
    {
        if ($value1 !== $value2) {
            $this->addError($field1, $message ?? "Os campos {$field1} e {$field2} devem ser iguais.");
            return false;
        }
        return true;
    }
}
