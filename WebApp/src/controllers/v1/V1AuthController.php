<?php

namespace controllers\v1;

use services\AuthService;
use helpers\ApiHelper;
use helpers\ApiResponse;
use Exception;
use JsonException;

class V1AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handles user registration.
     *
     * @throws JsonException
     */
    public function register(): void
    {
        try {
            // Validate that request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                ApiHelper::sendError(405, 'Method Not Allowed. Use POST.');
            }

            $input = ApiHelper::getJsonInput();
            $response = $this->authService->register($input);

            if ($response->success) {
                // Generate JWT token if user data contains ID and email
                if (!empty($response->data['id']) && !empty($response->data['email'])) {
                    $token = ApiHelper::generateJwtToken([
                        'user_id' => $response->data['id'],
                        'email' => $response->data['email']
                    ]);
                    $response->data['token'] = $token;
                }

                // Handle response based on request type (API or Web)
                if (ApiHelper::isApiRequest()) {
                    ApiHelper::sendApiResponse(201, $response);
                } else {
                    $_SESSION['success'] = 'User registered successfully.';
                    header("Location: /");
                    exit();
                }
            } else if (ApiHelper::isApiRequest()) {
                ApiHelper::sendApiResponse(400, $response);
            } else {
                $_SESSION['error'] = $response->message;
                header("Location: /register");
                exit();
            }
        } catch (Exception $e) {
            // Handle exceptions based on request type
            if (ApiHelper::isApiRequest()) {
                ApiHelper::sendError(500, 'Internal server error.', ['error' => $e->getMessage()]);
            } else {
                $_SESSION['error'] = 'An internal error occurred.';
                header("Location: /register");
                exit();
            }
        }
    }

    /**
     * Handles user login.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Method Not Allowed. Use POST.');
        }

        // Implementation for login logic
    }

    /**
     * Handles password change requests.
     */
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Method Not Allowed. Use POST.');
        }

        // Implementation for changing password
    }

    /**
     * Handles password reset requests.
     */
    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Method Not Allowed. Use POST.');
        }

        // Implementation for forgot password functionality
    }
}
