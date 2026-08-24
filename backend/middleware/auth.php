<?php
/**
 * =====================================================================
 * AUTH MIDDLEWARE
 * =====================================================================
 * Session-based authentication + role-based + ownership authorization.
 *
 * Include this file AFTER helpers/response.php in any endpoint that
 * needs a logged-in user, e.g.:
 *
 *   require_once __DIR__ . '/../helpers/response.php';
 *   require_once __DIR__ . '/../middleware/auth.php';
 *   $currentUser = requireLogin();
 *   requireRole($currentUser, ['landlord', 'admin']);
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie settings.
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // JS cannot read the session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Ensures a user is logged in (valid session). Sends 401 and exits if not.
 *
 * @return array{id:int, role:string, name:string, language:string}
 */
function requireLogin(): array
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        sendError('Unauthorized. Please log in.', 401);
    }

    return [
        'id'       => (int) $_SESSION['user_id'],
        'role'     => (string) $_SESSION['role'],
        'name'     => $_SESSION['name'] ?? '',
        'language' => $_SESSION['language'] ?? 'en',
    ];
}

/**
 * Ensures the current user's role is in $allowedRoles. Sends 403 if not.
 *
 * @param array $currentUser  Result of requireLogin()
 * @param array $allowedRoles e.g. ['admin'], ['landlord', 'admin']
 */
function requireRole(array $currentUser, array $allowedRoles): void
{
    if (!in_array($currentUser['role'], $allowedRoles, true)) {
        sendError('Forbidden. You do not have permission to perform this action.', 403);
    }
}

/**
 * Ownership check: confirms $currentUser owns the given resource OR is
 * an admin (admins can access everything). Sends 403 if unauthorized.
 *
 * @param array $currentUser
 * @param int   $resourceOwnerId  The user_id/tenant_id/landlord_id on the record
 */
function requireOwnershipOrAdmin(array $currentUser, int $resourceOwnerId): void
{
    if ($currentUser['role'] === 'admin') {
        return;
    }
    if ($currentUser['id'] !== $resourceOwnerId) {
        sendError('Forbidden. You do not have access to this resource.', 403);
    }
}

/**
 * Checks whether the current user is a participant (landlord or tenant)
 * of a given agreement, or an admin. Used for agreement-scoped resources
 * (complaints, repairs, payments, risk scores).
 *
 * @param array $currentUser
 * @param int   $landlordId
 * @param int   $tenantId
 */
function requireAgreementAccess(array $currentUser, int $landlordId, int $tenantId): void
{
    if ($currentUser['role'] === 'admin') {
        return;
    }
    if ($currentUser['id'] !== $landlordId && $currentUser['id'] !== $tenantId) {
        sendError('Forbidden. You are not a participant of this agreement.', 403);
    }
}
