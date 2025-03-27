<?php

namespace controllers\v1;

use JetBrains\PhpStorm\NoReturn;
use services\AuthService;
use helpers\ApiHelper;
use helpers\ApiResponse;
use managers\SessionManager;
use JsonException;
use Exception;

/**
 * Class V1AuthController
 * Provides secure endpoints for user authentication.
 * Includes registration, login, and logout using session and JWT.
 */
class V1AuthController
{
    private AuthService $authService;
    private SessionManager $sessionManager;

    /**
     * V1AuthController constructor.
     *
     * @param AuthService $authService
     * @param SessionManager $sessionManager
     */
    public function __construct(AuthService $authService, SessionManager $sessionManager)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Registers a new user via API.
     * Accepts form-data and optional profile picture.
     * Responds with success status and JWT.
     *
     * @return void
     * @throws JsonException
     */
    public function register(): void
    {
        ApiHelper::requirePost();
        //ApiHelper::requireApiToken();

        try {
            $input = $_POST;
            $response = $this->authService->register($input);

            if ($response->success) {
                $this->sessionManager->storeUser($response->data, $response->data['token'] ?? null);
            }

            ApiHelper::sendApiResponse($response->success ? 201 : 400, $response);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Logs in an existing user.
     * Accepts JSON with email and password.
     *
     * @return void
     * @throws JsonException
     */
    public function login(): void
    {
        ApiHelper::requirePost();

        try {
            $input = ApiHelper::getJsonInput();
            $response = $this->authService->login($input);

            if ($response->success) {
                $this->sessionManager->storeUser($response->data, $response->data['token'] ?? null);
            }

            ApiHelper::sendApiResponse($response->success ? 200 : 401, $response);
        } catch (JsonException|Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Logs out the current user and destroys session.
     *
     * @return void
     * @throws JsonException
     */
    #[NoReturn] public function logout(): void
    {
        $this->sessionManager->destroy();
        ApiHelper::sendApiResponse(200, new ApiResponse(true, 'Successfully logged out.'));
    }
}
