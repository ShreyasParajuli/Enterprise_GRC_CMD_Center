<?php
/**
 * Authentication and Authorization Helper Functions
 */

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

// Check if user has a specific role (or one of an array of roles)
function hasRole($roles) {
    if (!isLoggedIn() || !isset($_SESSION['user_role'])) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

// Redirect if user doesn't have the required role
function requireRole($roles) {
    requireLogin(); // Must be logged in first
    
    if (!hasRole($roles)) {
        // Log unauthorized access attempt (optional but recommended)
        header('HTTP/1.1 403 Forbidden');
        echo "403 Forbidden - You do not have permission to access this resource.";
        exit;
    }
}

// Attempt to login a user
function loginUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.password_hash, u.status, r.name as role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.username = ? OR u.email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] !== 'active') {
            return "Account is " . htmlspecialchars($user['status']) . ". Please contact administrator.";
        }
        
        // Prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role_name'] ?? 'User';
        
        // Log activity
        logActivity($pdo, $user['id'], "User Logged In");
        
        return true;
    }
    
    return "Invalid username or password.";
}

// Logout user
function logoutUser($pdo = null) {
    if (isset($_SESSION['user_id']) && $pdo) {
        logActivity($pdo, $_SESSION['user_id'], "User Logged Out");
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Log user activity
function logActivity($pdo, $userId, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $action, $ip]);
}
?>
