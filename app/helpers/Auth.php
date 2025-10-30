<?php
/**
 * Clase Auth - Autenticación y autorización
 */
class Auth {
    
    public static function login($username, $password) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $user = $db->fetchOne($sql, [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('full_name', $user['full_name']);
            Session::set('role', $user['role']);
            Session::set('last_activity', time());
            return true;
        }
        return false;
    }
    
    public static function logout() {
        Session::destroy();
    }
    
    public static function isLoggedIn() {
        if (!Session::has('user_id')) {
            return false;
        }
        
        // Verificar timeout de sesión
        $lastActivity = Session::get('last_activity', 0);
        if ((time() - $lastActivity) > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        
        Session::set('last_activity', time());
        return true;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    public static function requireRole($roles) {
        self::requireLogin();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $userRole = Session::get('role');
        if (!in_array($userRole, $roles)) {
            header('Location: ' . BASE_URL . '/access-denied');
            exit;
        }
    }
    
    public static function user() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'full_name' => Session::get('full_name'),
            'role' => Session::get('role')
        ];
    }
    
    public static function hasRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array(Session::get('role'), $roles);
    }
}
