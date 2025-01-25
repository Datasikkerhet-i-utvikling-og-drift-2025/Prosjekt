<?php

class InputValidator
{
    // Check if a field is not empty
    public static function isNotEmpty($value)
    {
        return isset($value) && trim($value) !== '';
    }

    // Validate email format
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validate string length (min and max)
    public static function isValidLength($value, $min, $max)
    {
        $length = strlen(trim($value));
        return $length >= $min && $length <= $max;
    }

    // Validate password strength
    public static function isValidPassword($password)
    {
        // Example: At least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special character
        return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }

    // Validate integer within a range
    public static function isValidInteger($value, $min = null, $max = null)
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
            return false;
        }

        return true;
    }

    // Sanitize a string to remove harmful characters
    public static function sanitizeString($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    // Sanitize email input
    public static function sanitizeEmail($email)
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    // Validate and sanitize an array of inputs
    public static function validateInputs($inputs, $rules)
    {
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
                }
            }

            // Add sanitized value if no errors for the field
            if (!isset($errors[$field])) {
                $sanitized[$field] = $value;
            }
        }

        return ['errors' => $errors, 'sanitized' => $sanitized];
    }
}
