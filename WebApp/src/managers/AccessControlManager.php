<?php

namespace helpers;

use JsonException;
use managers\SessionManager;

/**
 * Class AccessControl
 * Provides utility methods for enforcing access restrictions.
 */
class AccessControlManager
{
    /**
     * Require the user to be authenticated.
     * Sends HTTP 401 if not authenticated.
     * @throws JsonException
     */
    public static function requireLogin(): void
    {
        if (!SessionManager::isAuthenticated()) {
            ApiHelper::sendError(401, 'Unauthorized - Please log in.');
        }
    }

    /**
     * Require the user to have one of the given roles.
     *
     * @param array $allowedRoles
     * @throws JsonException
     */
    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();
        $role = SessionManager::getUserRole();
        if (!in_array($role, $allowedRoles, true)) {
            ApiHelper::sendError(403, 'Forbidden - You do not have permission.');
        }
    }

    /**
     * Check if the current user has a given role.
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        return SessionManager::getUserRole() === $role;
    }
}
