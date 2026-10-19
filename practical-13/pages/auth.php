<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';

define('SESSION_TIMEOUT', 15 * 60);
define('REMEMBER_DAYS', 30);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function field($key) {
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? $_POST[$key] : '';
}

function is_https() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function home_for_role($role) {
    return ($role === 'admin') ? 'admin.php' : 'dashboard.php';
}

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => is_https(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

function destroy_session() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function check_timeout() {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
            destroy_session();
            return true;
        }
        $_SESSION['last_activity'] = time();
    }
    return false;
}

function login_user($userId, $userName, $role) {
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['user_id']       = (int)$userId;
    $_SESSION['user']          = $userName;
    $_SESSION['role']          = $role;
    $_SESSION['last_activity'] = time();
}

function require_login($requiredRole = null) {
    start_secure_session();

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    if (check_timeout()) {
        header("Location: login.php?timeout=1");
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        try_remember_login();
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?login=required");
        exit;
    }

    if ($requiredRole !== null && ($_SESSION['role'] ?? '') !== $requiredRole) {
        http_response_code(403);
        echo "<h2>Access denied</h2>";
        echo "<p>You do not have permission to open this page.</p>";
        echo "<p><a href='" . e(home_for_role($_SESSION['role'] ?? 'student')) . "'>Go back</a></p>";
        exit;
    }
}

function clear_remember_cookie() {
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    unset($_COOKIE['remember_me']);
}

function issue_remember_token($conn, $userId) {
    $conn->query("DELETE FROM remember_tokens WHERE expires_at < " . time());

    $selector  = bin2hex(random_bytes(9));
    $validator = bin2hex(random_bytes(32));
    $hash      = hash('sha256', $validator);
    $expires   = time() + (REMEMBER_DAYS * 86400);

    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Remember token insert failed: " . $conn->error);
        return;
    }
    $stmt->bind_param("issi", $userId, $selector, $hash, $expires);
    $stmt->execute();
    $stmt->close();

    setcookie('remember_me', $selector . ':' . $validator, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function try_remember_login() {
    if (empty($_COOKIE['remember_me']) || !is_string($_COOKIE['remember_me'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
        clear_remember_cookie();
        return false;
    }
    $selector  = $parts[0];
    $validator = $parts[1];

    $conn = db_connect();
    if (!$conn) {
        return false;
    }

    $stmt = $conn->prepare(
        "SELECT t.id, t.token_hash, t.expires_at, u.id, u.`user`, u.role
         FROM remember_tokens t JOIN users u ON u.id = t.user_id
         WHERE t.selector = ? LIMIT 1"
    );
    if ($stmt === false) {
        $conn->close();
        return false;
    }
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $stmt->bind_result($tokenId, $tokenHash, $expiresAt, $userId, $userName, $role);
    $found = $stmt->fetch();
    $stmt->close();

    $ok = false;
    if ($found) {
        $del = $conn->prepare("DELETE FROM remember_tokens WHERE id = ?");
        if ($del) {
            $del->bind_param("i", $tokenId);
            $del->execute();
            $del->close();
        }

        if ($expiresAt >= time() && hash_equals($tokenHash, hash('sha256', $validator))) {
            login_user($userId, $userName, $role);
            issue_remember_token($conn, $userId);
            $ok = true;
        }
    }

    if (!$ok) {
        clear_remember_cookie();
    }
    $conn->close();
    return $ok;
}

function forget_remember_token() {
    if (!empty($_COOKIE['remember_me']) && is_string($_COOKIE['remember_me'])) {
        $parts    = explode(':', $_COOKIE['remember_me']);
        $selector = $parts[0];
        $conn = db_connect();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            if ($stmt) {
                $stmt->bind_param("s", $selector);
                $stmt->execute();
                $stmt->close();
            }
            $conn->close();
        }
    }
    clear_remember_cookie();
}