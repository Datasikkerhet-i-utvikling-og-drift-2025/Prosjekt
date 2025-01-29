<?php

class InputValidator
{

    // Validation method for registration
    public static function validateRegistration($input)
    {
        $rules = self::getRegistrationRules();
        $errors = [];
        $sanitized = [];

        foreach ($rules as $field => $ruleset) {
            $value = $input[$field] ?? '';
            $sanitized[$field] = self::sanitize($value);

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

        // Check if passwords match
        if ($sanitized['password'] !== ($sanitized['repeat_password'] ?? '')) {
            $errors[] = "Passwords do not match.";
        }

        return ['errors' => $errors, 'sanitized' => $sanitized];
    }

    // Define the validation rules for registration
    private static function getRegistrationRules()
    {
        return [
            'first_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'last_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'name' => ['required' => true, 'min' => 3, 'max' => 100],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 8],
            'repeat_password' => ['required' => true],
            'role' => ['required' => true],
            'study_program' => ['required' => false, 'max' => 100],
            'cohort_year' => ['required' => false, 'integer' => true],
        ];
    }

    //__________________________
    // Validation methods test
    public static function sanitize($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    // Validation methods
    private static function validateRequired($value, $ruleValue)
    {
        return $ruleValue && empty($value) ? 'This field is required.' : true;
    }

    private static function validateMin($value, $ruleValue)
    {
        return strlen($value) < $ruleValue ? "Must be at least $ruleValue characters." : true;
    }

    private static function validateMax($value, $ruleValue)
    {
        return strlen($value) > $ruleValue ? "Must be no more than $ruleValue characters." : true;
    }

    private static function validateEmail($value, $ruleValue)
    {
        return $ruleValue && !filter_var($value, FILTER_VALIDATE_EMAIL) ? 'Invalid email format.' : true;
    }

    // __________________________

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
        // TODO fix this regex
        return true;
        return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $password);
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

    // Sanitize URL input
    public static function sanitizeUrl($url)
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    // Validate and sanitize an array of inputs
    public static function validateRequest($requiredFields, $inputs)
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (empty($inputs[$field])) {
                $errors[] = "$field is required.";
            }
        }
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            exit;
        }
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

                    case 'regex':
                        if ($ruleValue && !preg_match($ruleValue, $value)) {
                            $errors[$field][] = 'Invalid format.';
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
