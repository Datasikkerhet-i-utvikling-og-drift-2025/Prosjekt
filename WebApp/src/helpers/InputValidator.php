<?php

namespace helpers;

class InputValidator
{
    /**
     * Validate registration input fields based on role-specific rules.
     *
     * @param array $input User-provided input data.
     * @return array Returns an array with 'errors' and 'sanitized' data.
     */
    public static function validateRegistration(array $input): array
    {
        $rules = self::getRegistrationRules($input['role'] ?? '');
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleset) {
            $value = $input[$field] ?? '';

            // Special case for email: Validate first, don't sanitize it like a normal string
            if ($field === 'email') {
                if (!self::isValidEmail($value)) {
                    $errors[$field][] = 'Invalid email format.';
                } else {
                    $sanitized[$field] = $value; // Store the actual email, not a boolean
                }
                continue;
            }

            // For other fields, sanitize normally
            $sanitized[$field] = self::sanitizeString($value);

            foreach ($ruleset as $rule => $ruleValue) {
                $validationMethod = 'validate' . ucfirst($rule);
                if (method_exists(self::class, $validationMethod)) {
                    $validationResult = self::$validationMethod($sanitized[$field], $ruleValue);
                    if ($validationResult !== true) {
                        $errors[$field][] = $validationResult;
                    }
                }
            }
        }

        // Ensure passwords match
        if ($sanitized['password'] !== ($sanitized['repeat_password'] ?? '')) {
            $errors['password'][] = "Passwords do not match.";
        }

        return ['errors' => $errors, 'sanitized' => $sanitized];
    }


    /**
     * Get validation rules for different user roles.
     *
     * @param string $role The user role ('student' or 'lecturer').
     * @return array The validation rules for the role.
     */
    private static function getRegistrationRules(string $role): array
    {
        $commonRules = [
            'first_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'last_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'name' => ['required' => true, 'min' => 3, 'max' => 100],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 8],
            'repeat_password' => ['required' => true],
            'role' => ['required' => true],
        ];

        if ($role === 'student') {
            return array_merge($commonRules, [
                'study_program' => ['required' => true, 'max' => 100],
                'cohort_year' => ['required' => true, 'integer' => true],
            ]);
        }

        if ($role === 'lecturer') {
            return array_merge($commonRules, [
                'course_code' => ['required' => true, 'max' => 10],
                'course_name' => ['required' => true, 'max' => 100],
                'course_pin' => ['required' => true, 'regex' => '/^\d{4}$/'],
            ]);
        }

        return $commonRules;
    }

    /**
     * Sanitize input value to remove harmful characters.
     *
     * @param string $value The input value.
     * @return string The sanitized string.
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if a field is not empty.
     *
     * @param mixed $value The value to check.
     * @return bool True if not empty, false otherwise.
     */
    public static function isNotEmpty(mixed $value): bool
    {
        return isset($value) && trim($value) !== '';
    }

    /**
     * Validate email format.
     *
     * @param string $email The email address.
     * @return bool True if valid, false otherwise.
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate string length (min and max).
     *
     * @param string $value The string to validate.
     * @param int $min Minimum length.
     * @param int $max Maximum length.
     * @return bool True if valid, false otherwise.
     */
    public static function isValidLength(string $value, int $min, int $max): bool
    {
        $length = strlen(trim($value));
        return $length >= $min && $length <= $max;
    }

    /**
     * Validate integer within a range.
     *
     * @param mixed $value The value to check.
     * @param int|null $min Minimum value.
     * @param int|null $max Maximum value.
     * @return bool True if valid, false otherwise.
     */
    public static function isValidInteger($value, ?int $min = null, ?int $max = null): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
            return false;
        }

        return true;
    }

    /**
     * Validate password strength.
     *
     * @param string $password The password to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function isValidPassword(string $password): bool
    {
        return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $password);
    }

    /**
     * Validate and sanitize an array of inputs.
     *
     * @param array $inputs The input data.
     * @param array $rules The validation rules.
     * @return array Returns an array with 'errors' and 'sanitized' data.
     */
    public static function validateInputs(array $inputs, array $rules): array
    {
        Logger::info("Validating inputs: " . var_export($inputs, true));

        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $inputs[$field] ?? '';

            foreach ($ruleSet as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && !self::isNotEmpty($value)) {
                            $errors[$field][] = 'This field is required.';
                        }
                        break;

                    case 'email':
                        if ($ruleValue && !self::isValidEmail($value)) {
                            $errors[$field][] = 'Invalid email format.';
                        }
                        break;

                    case 'min':
                        if (!self::isValidLength($value, $ruleValue, PHP_INT_MAX)) {
                            $errors[$field][] = "Must be at least $ruleValue characters long.";
                        }
                        break;

                    case 'max':
                        if (!self::isValidLength($value, 0, $ruleValue)) {
                            $errors[$field][] = "Must not exceed $ruleValue characters.";
                        }
                        break;

                    case 'password':
                        if ($ruleValue && !self::isValidPassword($value)) {
                            $errors[$field][] = 'Password must contain at least 8 characters, including an uppercase letter, a lowercase letter, a digit, and a special character.';
                        }
                        break;

                    case 'sanitize':
                        if ($ruleValue) {
                            $value = self::sanitizeString($value);
                        }
                        break;

                    case 'integer':
                        if ($ruleValue && !self::isValidInteger($value)) {
                            $errors[$field][] = 'Must be a valid integer.';
                        }
                        break;

                    case 'regex':
                        if ($ruleValue && !preg_match($ruleValue, $value)) {
                            $errors[$field][] = 'Invalid format.';
                        }
                        break;
                }
            }

            if (!isset($errors[$field])) {
                $sanitized[$field] = $value;
            }
        }

        return ['errors' => $errors, 'sanitized' => $sanitized];
    }
}
