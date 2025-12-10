<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Главная страница админ-панели
    public function dashboard()
    {
        // Проверяем авторизацию
        if (!php_auth_check()) {
            return redirect('/auth')->with('error', 'Войдите для доступа к админ-панели');
        }
        
        // Проверяем права администратора
        if (!php_auth_is_admin()) {
            abort(403, 'Доступ запрещен. Требуются права администратора.');
        }
        
        $stats = $this->getAdminStats();
        
        return view('admin.dashbord', compact('stats'));
    }
    
    // Список пользователей
    public function users(Request $request)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен. Требуются права администратора.');
        }
        
        $query = DB::table('users as u')
            ->select('u.*')
            ->orderBy('u.created_at', 'desc');
        
        // Фильтрация
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.username', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('u.fullname', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(20);
        
        // Получаем роли для каждого пользователя
        $users->getCollection()->transform(function ($user) {
            $user->roles = DB::table('user_role_assignments as ura')
                ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                ->where('ura.user_id', $user->user_id)
                ->pluck('ur.role_name')
                ->toArray();
            
            $user->role_ids = DB::table('user_role_assignments as ura')
                ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                ->where('ura.user_id', $user->user_id)
                ->pluck('ur.role_id')
                ->toArray();
            
            return $user;
        });
        
        // Получаем все доступные роли для назначения
        $availableRoles = DB::table('user_roles')->get();
        
        return view('admin.users', compact('users', 'availableRoles'));
    }
    
    // Список всех полей
public function fields(Request $request)
{
    if (!php_auth_check() || !php_auth_is_admin()) {
        abort(403, 'Доступ запрещен. Требуются права администратора.');
    }
    
    $query = DB::table('fields as f')
        ->join('users as u', 'f.user_id', '=', 'u.user_id')
        ->select('f.*', 'u.username', 'u.email', 'u.avatar_url')
        ->orderBy('f.created_at', 'desc');
    
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where('f.field_name', 'like', "%{$search}%");
    }
    
    if ($request->has('visibility') && $request->visibility) {
        $query->where('f.is_public', $request->visibility == 'public' ? 1 : 0);
    }
    
    if ($request->has('owner') && $request->owner) {
        $query->where('f.user_id', $request->owner);
    }
    
    $fields = $query->paginate(20);
    
    // Получаем всех пользователей для фильтра
    $users = DB::table('users')
        ->select('user_id', 'username')
        ->orderBy('username')
        ->get();
    
    // Получаем статистику для полей
    $stats = [
        'total_fields' => DB::table('fields')->count(),
        'public_fields' => DB::table('fields')->where('is_public', 1)->count(),
        'private_fields' => DB::table('fields')->where('is_public', 0)->count(),
        'total_area' => DB::table('fields')->sum('field_area') ?? 0,
    ];
    
    return view('admin.fields', compact('fields', 'users', 'stats'));
}
    
    // Получить список всех ролей
    public function roles()
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен. Требуются права администратора.');
        }
        
        $roles = DB::table('user_roles')->get();
        
        return view('admin.roles', compact('roles'));
    }
    
    // Статистика для админ-панели
    private function getAdminStats(): array
    {
        $stats = [];
        
        try {
            // Базовые статистики
            $stats['total_users'] = DB::table('users')->count();
            $stats['total_fields'] = DB::table('fields')->count();
            
            // Опциональные таблицы
            try {
                $stats['total_operations'] = DB::table('fields_has_operation')->count();
            } catch (\Exception $e) {
                $stats['total_operations'] = 0;
            }
            
            try {
                $stats['total_surveys'] = DB::table('agronomic_surveys')->count();
            } catch (\Exception $e) {
                $stats['total_surveys'] = 0;
            }
            
            // Пользователи за сегодня
            $stats['active_today'] = DB::table('users')
                ->whereDate('created_at', today())
                ->orWhereDate('updated_at', today())
                ->count();
            
            // Поля
            $stats['public_fields'] = DB::table('fields')->where('is_public', 1)->count();
            $stats['private_fields'] = DB::table('fields')->where('is_public', 0)->count();
            $stats['total_area'] = DB::table('fields')->sum('field_area') ?? 0;
            
            // Подсчет админов
            try {
                $adminRole = DB::table('user_roles')->where('role_name', 'admin')->first();
                if ($adminRole) {
                    $stats['admin_users'] = DB::table('user_role_assignments')
                        ->where('role_id', $adminRole->role_id)
                        ->distinct('user_id')
                        ->count('user_id');
                } else {
                    $stats['admin_users'] = 0;
                }
            } catch (\Exception $e) {
                $stats['admin_users'] = 0;
            }
            
            // Пользователи с полями
            $stats['users_with_fields'] = DB::table('users as u')
                ->join('fields as f', 'u.user_id', '=', 'f.user_id')
                ->distinct('u.user_id')
                ->count('u.user_id');
            
            // За последнюю неделю
            $stats['recent_users'] = DB::table('users')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();
            
            $stats['recent_fields'] = DB::table('fields')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();
                
        } catch (\Exception $e) {
            // Заполняем значения по умолчанию в случае ошибки
            $stats = [
                'total_users' => 0,
                'total_fields' => 0,
                'total_operations' => 0,
                'total_surveys' => 0,
                'active_today' => 0,
                'public_fields' => 0,
                'private_fields' => 0,
                'total_area' => 0,
                'admin_users' => 0,
                'users_with_fields' => 0,
                'recent_users' => 0,
                'recent_fields' => 0,
            ];
        }
        
        return $stats;
    }
    
    // Назначить роль пользователю
    public function assignRole(Request $request, $userId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $validated = $request->validate([
            'role_id' => 'required|exists:user_roles,role_id',
        ]);
        
        // Проверяем, не назначена ли уже эта роль
        $alreadyAssigned = DB::table('user_role_assignments')
            ->where('user_id', $userId)
            ->where('role_id', $validated['role_id'])
            ->exists();
        
        if (!$alreadyAssigned) {
            DB::table('user_role_assignments')->insert([
                'user_id' => $userId,
                'role_id' => $validated['role_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Обновляем сессию пользователя если он редактирует сам себя
            if ($userId == php_session('user_id')) {
                $this->updateUserRolesInSession($userId);
            }
            
            return redirect()->back()->with('success', 'Роль успешно назначена');
        }
        
        return redirect()->back()->with('error', 'Роль уже назначена пользователю');
    }
    
    // Удалить роль у пользователя
    public function removeRole($userId, $roleId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        DB::table('user_role_assignments')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
        
        // Обновляем сессию пользователя если он редактирует сам себя
        if ($userId == php_session('user_id')) {
            $this->updateUserRolesInSession($userId);
        }
        
        return redirect()->back()->with('success', 'Роль успешно удалена');
    }
    
    // Создать новую роль
    public function createRole(Request $request)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $validated = $request->validate([
            'role_name' => 'required|string|max:50|unique:user_roles,role_name',
        ]);
        
        DB::table('user_roles')->insert([
            'role_name' => $validated['role_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.roles')->with('success', 'Роль создана');
    }
    
    // Удалить пользователя
    public function deleteUser($userId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        // Нельзя удалить самого себя
        if ($userId == php_session('user_id')) {
            return redirect()->back()->with('error', 'Нельзя удалить самого себя');
        }
        
        DB::table('users')->where('user_id', $userId)->delete();
        
        return redirect()->route('admin.users')->with('success', 'Пользователь удален');
    }
    
    // Удалить поле
    public function deleteField($fieldId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        DB::table('fields')->where('field_id', $fieldId)->delete();
        
        return redirect()->route('admin.fields')->with('success', 'Поле удалено');
    }
    
    // Удалить роль
    public function deleteRole($roleId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        // Нельзя удалять системные роли
        $systemRoles = ['admin', 'user'];
        $role = DB::table('user_roles')->where('role_id', $roleId)->first();
        
        if ($role && in_array($role->role_name, $systemRoles)) {
            return redirect()->back()->with('error', 'Нельзя удалить системную роль');
        }
        
        // Проверяем, есть ли пользователи с этой ролью
        $hasUsers = DB::table('user_role_assignments')
            ->where('role_id', $roleId)
            ->exists();
        
        if ($hasUsers) {
            return redirect()->back()->with('error', 'Нельзя удалить роль, у которой есть пользователи');
        }
        
        DB::table('user_roles')->where('role_id', $roleId)->delete();
        
        return redirect()->route('admin.roles')->with('success', 'Роль удалена');
    }
    
    // Активировать пользователя
    public function activateUser($userId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        // Проверяем, есть ли поле is_active
        $columns = DB::select('SHOW COLUMNS FROM users');
        $hasIsActive = collect($columns)->contains(function ($column) {
            return $column->Field === 'is_active';
        });
        
        if ($hasIsActive) {
            DB::table('users')->where('user_id', $userId)->update([
                'is_active' => 1,
                'updated_at' => now(),
            ]);
        }
        
        return redirect()->back()->with('success', 'Пользователь активирован');
    }
    
    // Деактивировать пользователя
    public function deactivateUser($userId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        // Нельзя деактивировать самого себя
        if ($userId == php_session('user_id')) {
            return redirect()->back()->with('error', 'Нельзя деактивировать самого себя');
        }
        
        // Проверяем, есть ли поле is_active
        $columns = DB::select('SHOW COLUMNS FROM users');
        $hasIsActive = collect($columns)->contains(function ($column) {
            return $column->Field === 'is_active';
        });
        
        if ($hasIsActive) {
            DB::table('users')->where('user_id', $userId)->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        }
        
        return redirect()->back()->with('success', 'Пользователь деактивирован');
    }
    
    // Обновить роли пользователя в сессии
    private function updateUserRolesInSession($userId): void
    {
        $roles = DB::table('user_role_assignments as ura')
            ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
            ->where('ura.user_id', $userId)
            ->pluck('ur.role_name')
            ->toArray();
        
        php_session_set('roles', $roles);
    }
    // Экспорт пользователей
public function exportUsers(Request $request)
{
    if (!php_auth_check() || !php_auth_is_admin()) {
        abort(403, 'Доступ запрещен.');
    }
    
    $query = DB::table('users as u')
        ->leftJoin('fields as f', 'u.user_id', '=', 'f.user_id')
        ->select(
            'u.user_id as id',
            'u.username',
            'u.email',
            'u.fullname',
            'u.created_at',
            DB::raw('COUNT(f.field_id) as fields_count'),
            DB::raw('(SELECT GROUP_CONCAT(ur.role_name) 
                      FROM user_role_assignments ura 
                      JOIN user_roles ur ON ura.role_id = ur.role_id 
                      WHERE ura.user_id = u.user_id) as roles')
        )
        ->groupBy('u.user_id');
    
    // Применяем фильтры
    if ($request->has('filters')) {
        $filters = $request->input('filters', []);
        
        if (in_array('with_fields', $filters)) {
            $query->having('fields_count', '>', 0);
        }
        
        if (in_array('with_roles', $filters)) {
            $query->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_role_assignments')
                    ->whereRaw('user_role_assignments.user_id = u.user_id');
            });
        }
        
        if (in_array('active_only', $filters)) {
            $query->where('u.is_active', 1);
        }
    }
    
    // Фильтр по дате
    if ($request->has('start_date')) {
        $query->whereDate('u.created_at', '>=', $request->start_date);
    }
    
    if ($request->has('end_date')) {
        $query->whereDate('u.created_at', '<=', $request->end_date);
    }
    
    $users = $query->get();
    
    // Форматируем данные для экспорта
    $exportData = [];
    $selectedColumns = $request->input('columns', ['id', 'username', 'email', 'created_at']);
    
    foreach ($users as $user) {
        $row = [];
        
        if (in_array('id', $selectedColumns)) {
            $row['ID'] = $user->id;
        }
        
        if (in_array('username', $selectedColumns)) {
            $row['Имя пользователя'] = $user->username;
        }
        
        if (in_array('email', $selectedColumns)) {
            $row['Email'] = $user->email;
        }
        
        if (in_array('fullname', $selectedColumns)) {
            $row['Полное имя'] = $user->fullname ?? '-';
        }
        
        if (in_array('roles', $selectedColumns)) {
            $row['Роли'] = $user->roles ?? '-';
        }
        
        if (in_array('created_at', $selectedColumns)) {
            $row['Дата регистрации'] = date('d.m.Y H:i', strtotime($user->created_at));
        }
        
        if (in_array('fields_count', $selectedColumns)) {
            $row['Количество полей'] = $user->fields_count;
        }
        
        $exportData[] = $row;
    }
    
    $format = $request->input('format', 'csv');
    $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.' . $format;
    
    if ($format === 'csv') {
        return $this->exportToCSV($exportData, $filename);
    } elseif ($format === 'excel') {
        return $this->exportToExcel($exportData, $filename);
    } elseif ($format === 'pdf') {
        return $this->exportToPDF($exportData, $filename);
    }
    
    return redirect()->back()->with('error', 'Неверный формат экспорта');
}

private function exportToCSV($data, $filename)
{
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $callback = function() use ($data) {
        $file = fopen('php://output', 'w');
        fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM для UTF-8
        
        if (!empty($data)) {
            // Заголовки
            fputcsv($file, array_keys($data[0]), ';');
            
            // Данные
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

private function exportToExcel($data, $filename)
{
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $output = '';
    
    if (!empty($data)) {
        // Заголовки
        $output .= implode("\t", array_keys($data[0])) . "\n";
        
        // Данные
        foreach ($data as $row) {
            $output .= implode("\t", array_map(function($value) {
                return str_replace(["\t", "\r", "\n"], ' ', $value);
            }, $row)) . "\n";
        }
    }
    
    return response($output, 200, $headers);
}

private function exportToPDF($data, $filename)
{
    $html = '<h1>Экспорт пользователей</h1>';
    $html .= '<table border="1" cellpadding="5">';
    
    if (!empty($data)) {
        // Заголовки
        $html .= '<tr>';
        foreach (array_keys($data[0]) as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';
        
        // Данные
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
    }
    
    $html .= '</table>';
    
    $headers = [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    return response($html, 200, $headers);
}
}
