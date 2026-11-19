<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $method = 'validate' . str_replace('_', '', ucwords($ruleName, '_'));
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $params);
                }
            }
        }
    }

    private function validateRequired(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
    }

    private function validateEmail(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Please enter a valid email address.');
        }
    }

    private function validateMin(string $field, mixed $value, array $params): void
    {
        $min = (int)($params[0] ?? 0);
        if (!empty($value) && strlen((string)$value) < $min) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
        }
    }

    private function validateMax(string $field, mixed $value, array $params): void
    {
        $max = (int)($params[0] ?? 0);
        if (!empty($value) && strlen((string)$value) > $max) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.");
        }
    }

    private function validateMatches(string $field, mixed $value, array $params): void
    {
        $targetField = $params[0] ?? '';
        $targetValue = $this->data[$targetField] ?? null;
        if ($value !== $targetValue) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' does not match ' . str_replace('_', ' ', $targetField) . '.');
        }
    }

    private function validateUnique(string $field, mixed $value, array $params): void
    {
        if (empty($value)) {
            return;
        }

        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
        $binds = ['val' => $value];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore_id";
            $binds['ignore_id'] = $ignoreId;
        }

        try {
            $count = (int)Database::fetchValue($sql, $binds);
            if ($count > 0) {
                $this->addError($field, 'This ' . str_replace('_', ' ', $field) . ' is already in use.');
            }
        } catch (\Throwable $e) {
            // If table doesn't exist yet during install, ignore
        }
    }

    private function validateIn(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !in_array($value, $params, true)) {
            $this->addError($field, 'Invalid value selected for ' . str_replace('_', ' ', $field) . '.');
        }
    }

    private function validateNumeric(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a number.');
        }
    }

    private function validateUrl(string $field, mixed $value, array $params): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'Please enter a valid URL.');
        }
    }

    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if (!empty($messages)) {
                return $messages[0];
            }
        }

        return null;
    }

    public function flash(): void
    {
        Session::flash('errors', $this->errors);
        foreach ($this->data as $key => $val) {
            if (!in_array($key, ['password', 'password_confirmation', 'csrf_token'], true) && is_scalar($val)) {
                Session::flash('old_' . $key, (string)$val);
            }
        }
    }
}
