<?php
/**
 * Vitanova — Authentification & Sessions
 * Helpers: isLoggedIn, isAdmin, requireLogin, requireAdmin, CSRF
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// Vérifications de session
// ============================================================

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin(string $redirect = '/compte.php'): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = MSG_ACCESS_RESTRICTED;
        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $base . $redirect);
        exit;
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        require_once __DIR__ . '/messages.php';
        http_response_code(403);
        die('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Accès refusé — Vitanova</title>
        <style>body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f7fdf9;margin:0}
        .box{text-align:center;padding:3rem;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(15,110,86,.08);max-width:400px}
        h1{color:#a32d2d;font-size:2rem;margin-bottom:1rem}p{color:#4a5568}
        a{color:#0f6e56;font-weight:600;text-decoration:none}</style></head>
        <body><div class="box"><h1>403</h1><p>' . MSG_ADMIN_UNAUTHORIZED . '</p>
        <a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/">← Retour à l\'accueil</a></div></body></html>');
    }
}

// ============================================================
// CSRF Token
// ============================================================

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

function validateCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function checkCsrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Token CSRF invalide.']));
    }
}

// ============================================================
// Flash messages
// ============================================================

function setFlash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string
{
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

// ============================================================
// Connexion utilisateur
// ============================================================

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

function logoutUser(): void
{
    // 1. Ensure session is active before touching it
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // 2. Clear all session data
    $_SESSION = [];
    // 3. Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    // 4. Destroy the old session
    session_destroy();
    // 5. Start a brand-new session and give it a fresh ID
    session_start();
    session_regenerate_id(true);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}
