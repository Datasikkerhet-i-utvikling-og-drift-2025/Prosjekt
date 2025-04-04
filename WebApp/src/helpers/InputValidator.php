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

        $result = self::validateInputs($input, $rules);

        if (
            isset($result['sanitized']['password'], $result['sanitized']['repeatPassword']) &&
            $result['sanitized']['password'] !== $result['sanitized']['repeatPassword']
        ) {
            $result['errors']['password'][] = "Passwords do not match.";
        }

        return $result;
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
            'firstName' => [
                'required' => true,
                'min'      => 3,
                'max'      => 50
            ],
            'lastName' => [
                'required' => true,
                'min'      => 3,
                'max'      => 50
            ],
            'email' => [
                'required' => true,
                'email'    => true
            ],
            'password' => [
                'required' => true,
                'min'      => 8
            ],
            'repeatPassword' => [
                'required' => true
            ],
            'role' => [
                'required' => true
            ],
        ];

        if ($role === 'student') {
            $commonRules['studyProgram'] = [
                'required' => true,
                'max'      => 100
            ];
            $commonRules['enrollmentYear'] = [
                'required' => true,
                'integer'  => true
            ];
        } elseif ($role === 'lecturer') {
            $commonRules['courseCode'] = [
                'required' => true,
                'max'      => 10
            ];
            $commonRules['courseName'] = [
                'required' => true,
                'max'      => 100
            ];
            $commonRules['coursePin'] = [
                'required' => true,
                'regex'    => '/^\d{4}$/'
            ];
        }

        return $commonRules;
    }

    /**
     * Validate and sanitize an array of inputs using a structured set of rules.
     *
     * @param array $inputs The input data (e.g. $_POST).
     * @param array $rules  The validation rules.
     * @return array        Returns an array with 'errors' and 'sanitized' data.
     */
    public static function validateInputs(array $inputs, array $rules): array
    {
        $errors    = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $inputs[$field] ?? '';

            // We'll sanitize every field (except if it's file upload).
            $value = self::sanitizeString($value);

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

        return [
            'errors'    => $errors,
            'sanitized' => $sanitized
        ];
    }

    /**
     * Sanitize input value by trimming and encoding special chars.
     *
     * @param string $value The input value
     * @return string Sanitized string
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if a string is not empty (after trim).
     *
     * @param string $value The value to check
     * @return bool True if not empty
     */
    public static function isNotEmpty(string $value): bool
    {
        return $value !== '';
    }

    /**
     * Validate email format.
     *
     * @param string $email The email address.
     * @return bool True if valid
     */
    public static function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate string length (min, max).
     *
     * @param string $value The string to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool True if valid
     */
    public static function isValidLength(string $value, int $min, int $max): bool
    {
        $length = strlen($value);
        return ($length >= $min && $length <= $max);
    }

    /**
     * Validate that a string is an integer. Optional min/max checks can be done in your code if needed.
     *
     * @param string $value The value to check
     * @return bool True if valid
     */
    public static function isValidInteger(string $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_INT);
    }
}
