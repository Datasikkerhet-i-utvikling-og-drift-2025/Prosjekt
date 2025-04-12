<?php

namespace controllers\v1;

use JetBrains\PhpStorm\NoReturn;
use services\AuthService;
use helpers\ApiHelper;
use helpers\ApiResponse;
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

    /**
     * V1AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
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
        ApiHelper::requireApiKey();

        try {
            $input = ApiHelper::getInput();

            $response = $this->authService->register($input);

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
            $input = ApiHelper::getInput();
            $response = $this->authService->login($input);


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
        //$this->sessionManager->destroy();
        ApiHelper::sendApiResponse(200, new ApiResponse(true, 'Successfully logged out.'));
    }

    public function requestPasswordReset(): void
    {
        ApiHelper::requirePost();


        try {
            $input = ApiHelper::getInput(); // Henter JSON-data fra request body

            if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                ApiHelper::sendError(400, 'Invalid or missing email address.');
                return; // Avslutt tidlig
            }

            // Kall en metode i AuthService som håndterer logikken
            // Denne metoden bør returnere en ApiResponse
            $response = $this->authService->handlePasswordResetRequest($input);

            // Send respons - vanligvis 200 OK uansett om e-posten finnes,
            // for å unngå å avsløre hvilke e-poster som er registrert.
            // AuthService bør formulere en passende melding (f.eks. "Hvis e-posten finnes...")
            ApiHelper::sendApiResponse(200, $response);

        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            // Logg gjerne $e->getMessage() for feilsøking
            ApiHelper::sendError(500, 'Internal server error during password reset request.');
        }
    }

    /**
     * Handles the actual password reset using a token.
     * Expects POST with JSON containing 'token' and 'new_password'.
     * Triggers the AuthService to validate the token and update the password.
     *
     * @return void
     * @throws JsonException
     */
    public function resetPassword(): void
    {
        ApiHelper::requirePost();


        try {
            $input = ApiHelper::getInput(); // Henter JSON-data

            // Enkel validering av input her
            if (!isset($input['token']) || empty(trim($input['token']))) {
                ApiHelper::sendError(400, 'Reset token is missing or empty.');
                return;
            }
            if (!isset($input['new_password']) || empty($input['new_password'])) {
                ApiHelper::sendError(400, 'New password is required.');
                return;
            }
            // Du kan legge til en sjekk for confirm_password her hvis API-et skal motta det,
            // men det er ofte bedre å kun validere det i frontend/UI-laget
            // if (!isset($input['confirm_password']) || $input['new_password'] !== $input['confirm_password']) {
            //    ApiHelper::sendError(400, 'Passwords do not match.');
            //    return;
            // }


            // Kall en metode i AuthService som validerer token og oppdaterer passordet
            // Denne metoden bør returnere en ApiResponse
            $response = $this->authService->handlePasswordReset($input);

            // Send respons basert på om det var vellykket i AuthService
            // AuthService bør sette $response->success til false ved ugyldig token, svakt passord etc.
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);

        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            // Logg gjerne $e->getMessage() for feilsøking
            ApiHelper::sendError(500, 'Internal server error during password reset execution.');
        }
    }


}
