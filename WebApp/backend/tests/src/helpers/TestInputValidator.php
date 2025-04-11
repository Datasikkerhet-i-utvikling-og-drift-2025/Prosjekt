<?php

require_once __DIR__ . '/../../../src/helpers/InputValidator.php';

// Helper function to assert test results
function assertTest($condition, $testName)
{
    if ($condition) {
        echo "[PASS] $testName\n";
    } else {
        echo "[FAIL] $testName\n";
    }
}

// Test cases for InputValidator
function runInputValidatorTests()
{
    echo "Running tests for InputValidator...\n";

    // Test isNotEmpty
    assertTest(InputValidator::isNotEmpty('Hello') === true, "isNotEmpty with non-empty string");
    assertTest(InputValidator::isNotEmpty('') === false, "isNotEmpty with empty string");
    assertTest(InputValidator::isNotEmpty(null) === false, "isNotEmpty with null");

    // Test isValidEmail
    assertTest(InputValidator::isValidEmail('test@example.com') === true, "isValidEmail with valid email");
    assertTest(InputValidator::isValidEmail('invalid-email') === false, "isValidEmail with invalid email");

    // Test isValidLength
    assertTest(InputValidator::isValidLength('Hello', 3, 10) === true, "isValidLength with valid range");
    assertTest(InputValidator::isValidLength('Hi', 3, 10) === false, "isValidLength too short");
    assertTest(InputValidator::isValidLength('HelloWorld!', 3, 10) === false, "isValidLength too long");

    // Test isValidPassword
    assertTest(InputValidator::isValidPassword('Secure@123') === true, "isValidPassword with valid password");
    assertTest(InputValidator::isValidPassword('weakpass') === false, "isValidPassword with weak password");

    // Test isValidInteger
    assertTest(InputValidator::isValidInteger(5, 1, 10) === true, "isValidInteger within range");
    assertTest(InputValidator::isValidInteger(15, 1, 10) === false, "isValidInteger out of range");
    assertTest(InputValidator::isValidInteger('not-an-integer') === false, "isValidInteger with invalid input");

    // Test sanitizeString
    $sanitizedString = InputValidator::sanitizeString('<script>alert("xss")</script>');
    assertTest($sanitizedString === '&lt;script&gt;alert("xss")&lt;/script&gt;', "sanitizeString with XSS input");

    // Test sanitizeEmail
    $sanitizedEmail = InputValidator::sanitizeEmail('  test@example.com  ');
    assertTest($sanitizedEmail === 'test@example.com', "sanitizeEmail with extra spaces");

    // Test sanitizeUrl
    $sanitizedUrl = InputValidator::sanitizeUrl('http://example.com/?q=<script>');
    assertTest($sanitizedUrl === 'http://example.com/?q=%3Cscript%3E', "sanitizeUrl with unsafe characters");

    // Test validateInputs
    $inputs = [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Secure@123',
    ];
    $rules = [
        'name' => ['required' => true, 'min' => 3, 'max' => 50],
        'email' => ['required' => true, 'email' => true],
        'password' => ['required' => true, 'password' => true],
    ];
    $validationResult = InputValidator::validateInputs($inputs, $rules);
    assertTest(empty($validationResult['errors']), "validateInputs with valid inputs");

    $invalidInputs = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'weakpass',
    ];
    $validationResult = InputValidator::validateInputs($invalidInputs, $rules);
    assertTest(!empty($validationResult['errors']), "validateInputs with invalid inputs");

    echo "Finished tests for InputValidator.\n";
}

// Run the tests
runInputValidatorTests();
