<?php
declare(strict_types=1);

namespace App\Support;

final class Validator
{
    public static function validate(array $input, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $input[$field] ?? null;
            $fieldRules = is_array($fieldRules) ? $fieldRules : explode('|', (string) $fieldRules);

            foreach ($fieldRules as $rule) {
                [$name, $parameter] = array_pad(explode(':', (string) $rule, 2), 2, null);
                $name = trim($name);

                if ($name === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = 'Поле обязательно для заполнения.';
                }

                if ($value === null || $value === '') {
                    continue;
                }

                if ($name === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Укажите корректный email.';
                }

                if ($name === 'string' && !is_string($value)) {
                    $errors[$field][] = 'Поле должно быть строкой.';
                }

                if ($name === 'min' && is_string($value) && mb_strlen($value) < (int) $parameter) {
                    $errors[$field][] = 'Слишком короткое значение.';
                }

                if ($name === 'max' && is_string($value) && mb_strlen($value) > (int) $parameter) {
                    $errors[$field][] = 'Слишком длинное значение.';
                }

                if ($name === 'date' && strtotime((string) $value) === false) {
                    $errors[$field][] = 'Укажите корректную дату.';
                }

                if ($name === 'in' && $parameter !== null) {
                    $allowed = array_map('trim', explode(',', $parameter));
                    if (!in_array((string) $value, $allowed, true)) {
                        $errors[$field][] = 'Выбрано недопустимое значение.';
                    }
                }

                if ($name === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field][] = 'Укажите корректный URL.';
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $input;
    }
}

