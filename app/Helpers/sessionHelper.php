<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Получить значение из PHP сессии
 */
if (!function_exists('php_session')) {
    function php_session($key = null, $default = null)
    {
        // Всегда запускаем сессию если не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (is_null($key)) {
            return $_SESSION;
        }
        
        // Поддержка dot notation (php_session('user.name'))
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $_SESSION;
            
            foreach ($keys as $k) {
                if (isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
        
        return $_SESSION[$key] ?? $default;
    }
}

/**
 * Установить значение в PHP сессию
 */
if (!function_exists('php_session_set')) {
    function php_session_set($key, $value)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Поддержка dot notation
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $session = &$_SESSION;
            
            foreach ($keys as $k) {
                if (!isset($session[$k]) || !is_array($session[$k])) {
                    $session[$k] = [];
                }
                $session = &$session[$k];
            }
            
            $session = $value;
        } else {
            $_SESSION[$key] = $value;
        }
        
        session_write_close();
        return true;
    }
}

/**
 * Удалить значение из PHP сессии
 */
if (!function_exists('php_session_remove')) {
    function php_session_remove($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $session = &$_SESSION;
            $lastKey = array_pop($keys);
            
            foreach ($keys as $k) {
                if (!isset($session[$k])) {
                    return false;
                }
                $session = &$session[$k];
            }
            
            unset($session[$lastKey]);
        } else {
            unset($_SESSION[$key]);
        }
        
        session_write_close();
        return true;
    }
}

/**
 * Проверить авторизацию через PHP сессию
 */
if (!function_exists('php_auth_check')) {
    function php_auth_check()
    {
        $userId = php_session('user_id');
        return !empty($userId) && $userId > 0;
    }
}

/**
 * Получить данные авторизованного пользователя
 */
if (!function_exists('php_auth_user')) {
    function php_auth_user($key = null)
    {
        if (!php_auth_check()) {
            return null;
        }
        
        $userData = [
            'id' => php_session('user_id'),
            'username' => php_session('username'),
            'email' => php_session('email'),
            'fullname' => php_session('fullname'),
            'avatar_url' => php_session('avatar_url'),
            'logged_in_at' => php_session('logged_in_at'),
        ];
        
        // Если нужен конкретный ключ
        if ($key) {
            return $userData[$key] ?? null;
        }
        
        return $userData;
    }
}

/**
 * Загрузить полную модель пользователя из БД
 */
if (!function_exists('php_auth_model')) {
    function php_auth_model()
    {
        if (!php_auth_check()) {
            return null;
        }
        
        return User::find(php_session('user_id'));
    }
}

/**
 * Выйти из системы (очистить PHP сессию)
 */
if (!function_exists('php_auth_logout')) {
    function php_auth_logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Очищаем все данные сессии
        $_SESSION = [];
        
        // Уничтожаем сессию
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        session_destroy();
        return true;
    }
}

/**
 * Проверить есть ли значение в сессии
 */
if (!function_exists('php_session_has')) {
    function php_session_has($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $_SESSION;
            
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return false;
                }
                $value = $value[$k];
            }
            
            return true;
        }
        
        return isset($_SESSION[$key]);
    }
}

/**
 * прочитать и удалить
 */
if (!function_exists('php_session_flash')) {
    function php_session_flash($key, $default = null)
    {
        $value = php_session($key, $default);
        php_session_remove($key);
        return $value;
    }
}
/**
 * Получить все роли пользователя
 */
if (!function_exists('php_auth_roles')) {
    function php_auth_roles()
    {
        return php_session('roles', []);
    }
}

/**
 * Проверить есть ли у пользователя конкретная роль
 */
if (!function_exists('php_auth_has_role')) {
    function php_auth_has_role($roleName)
    {
        $roles = php_auth_roles();
        return in_array($roleName, $roles);
    }
}

/**
 * Проверить есть ли у пользователя хотя бы одна из ролей
 */
if (!function_exists('php_auth_has_any_role')) {
    function php_auth_has_any_role($roleNames)
    {
        if (!is_array($roleNames)) {
            $roleNames = [$roleNames];
        }
        
        $userRoles = php_auth_roles();
        
        foreach ($roleNames as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Проверить есть ли у пользователя все указанные роли
 */
if (!function_exists('php_auth_has_all_roles')) {
    function php_auth_has_all_roles($roleNames)
    {
        if (!is_array($roleNames)) {
            $roleNames = [$roleNames];
        }
        
        $userRoles = php_auth_roles();
        
        foreach ($roleNames as $role) {
            if (!in_array($role, $userRoles)) {
                return false;
            }
        }
        
        return true;
    }
}

/**
 * Проверить является ли пользователь администратором
 */
if (!function_exists('php_auth_is_admin')) {
    function php_auth_is_admin()
    {
        // Сначала проверяем в сессии
        $sessionRoles = php_session('roles', []);
        if (in_array('admin', $sessionRoles)) {
            return true;
        }
        
        // Если нет в сессии, проверяем в БД
        $userId = php_session('user_id');
        if (!$userId) {
            return false;
        }
        
        try {
            // Проверяем в БД напрямую
            $isAdminInDB = DB::table('user_role_assignments as ura')
                ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                ->where('ura.user_id', $userId)
                ->where('ur.role_name', 'admin')
                ->exists();
            
            // Если есть в БД, обновляем сессию
            if ($isAdminInDB) {
                // Получаем все роли пользователя из БД
                $dbRoles = DB::table('user_role_assignments as ura')
                    ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                    ->where('ura.user_id', $userId)
                    ->pluck('ur.role_name')
                    ->toArray();
                
                // Обновляем сессию
                php_session_set('roles', $dbRoles);
            }
            
            return $isAdminInDB;
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 * Проверить является ли пользователь модератором
 */
if (!function_exists('php_auth_is_moderator')) {
    function php_auth_is_moderator()
    {
        return php_auth_has_role('moderator');
    }
}

/**
 * Получить иконку роли
 */
if (!function_exists('php_auth_role_icon')) {
    function php_auth_role_icon($roleName = null)
    {
        $roleIcons = [
            'admin' => '👑',
            'moderator' => '⚡',
            'author' => '📝',
            'user' => '👤',
            'premium' => '⭐'
        ];
        
        if ($roleName) {
            return $roleIcons[$roleName] ?? '👤';
        }
        
        // Возвращаем первую роль с иконкой
        $roles = php_auth_roles();
        foreach ($roles as $role) {
            if (isset($roleIcons[$role])) {
                return $roleIcons[$role];
            }
        }
        
        return '👤';
    }
}

/**
 * Получить отображаемое название роли
 */
if (!function_exists('php_auth_role_label')) {
    function php_auth_role_label($roleName = null)
    {
        $roleLabels = [
            'admin' => 'Администратор',
            'moderator' => 'Модератор',
            'author' => 'Автор',
            'user' => 'Пользователь',
            'premium' => 'Премиум пользователь'
        ];
        
        if ($roleName) {
            return $roleLabels[$roleName] ?? $roleName;
        }
        
        // Возвращаем первую роль с названием
        $roles = php_auth_roles();
        foreach ($roles as $role) {
            if (isset($roleLabels[$role])) {
                return $roleLabels[$role];
            }
        }
        
        return 'Пользователь';
    }
}

if (!function_exists('getCropColor')) {
    function getCropColor($cropName) {
        $colors = [
            'Пшеница' => '#8B4513',
            'Кукуруза' => '#FFD700',
            'Соя' => '#228B22',
            'Ячмень' => '#DAA520',
            'Рожь' => '#A0522D',
            'Овес' => '#F5DEB3',
            'Подсолнечник' => '#FF8C00',
            'Рапс' => '#FFD700',
            'Картофель' => '#8A2BE2',
            'default' => '#47866A'
        ];
        
        return $colors[$cropName] ?? $colors['default'];
    }
}

if (!function_exists('convertLevelToNumber')) {
    function convertLevelToNumber($level) {
        if (is_numeric($level)) {
            return (int)$level;
        }
        
        $levels = [
            'низкий' => 2,
            'средний' => 5,
            'высокий' => 8,
            'очень высокий' => 10,
            'низкая' => 2,
            'средняя' => 5,
            'высокая' => 8,
            'очень высокая' => 10
        ];
        
        $levelLower = mb_strtolower($level, 'UTF-8');
        return $levels[$levelLower] ?? 0;
    }
}

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        $classes = [
            'active' => 'active',
            'completed' => 'completed',
            'planned' => 'planned',
            'problem' => 'problem',
            'archived' => 'completed'
        ];
        
        return $classes[$status] ?? 'active';
    }
}

if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        $texts = [
            'active' => 'Активно',
            'completed' => 'Завершено',
            'planned' => 'Планируется',
            'problem' => 'Проблемы',
            'archived' => 'В архиве'
        ];
        
        return $texts[$status] ?? 'Активно';
    }
}

if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        $texts = [
            'active' => 'Активно',
            'completed' => 'Завершено',
            'planned' => 'Планируется',
            'archived' => 'В архиве'
        ];
        
        return $texts[$status] ?? 'Активно';
    }
}