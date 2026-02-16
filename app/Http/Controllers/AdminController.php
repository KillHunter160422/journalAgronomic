<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Импорт пользователей
     */
    public function importUsers(Request $request)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'skip_duplicates' => 'nullable|boolean',
            'send_welcome_email' => 'nullable|boolean',
            'assign_default_role' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.users')
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = $request->file('import_file');
        $skipDuplicates = $request->boolean('skip_duplicates', true);
        $assignDefaultRole = $request->boolean('assign_default_role', true);
        
        try {
            if ($file->getClientOriginalExtension() === 'csv') {
                $results = $this->importFromCSV($file, $skipDuplicates, $assignDefaultRole);
            } else {
                $results = $this->importFromExcel($file, $skipDuplicates, $assignDefaultRole);
            }
            
            $successMessage = "Импорт завершен. Успешно импортировано: {$results['imported']} пользователей";
            
            if ($results['skipped'] > 0) {
                $successMessage .= ", пропущено: {$results['skipped']} пользователей";
            }
            
            if (!empty($results['errors'])) {
                $request->session()->flash('import_errors', $results['errors']);
            }
            
            return redirect()->route('admin.users')
                ->with('import_success', $successMessage);
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->with('error', 'Ошибка импорта: ' . $e->getMessage());
        }
    }
    
    /**
     * Скачать шаблон для импорта
     */
    public function downloadImportTemplate()
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $filename = 'users_import_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Заголовки
            fputcsv($file, ['email', 'username', 'fullname']);
            
            // Примеры данных
            fputcsv($file, ['ivan@example.com', 'ivan', 'Иван Иванов']);
            fputcsv($file, ['petr@example.com', 'petr', 'Петр Петров']);
            fputcsv($file, ['anna@example.com', 'anna', 'Анна Смирнова']);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    // --- ПРИВАТНЫЕ ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ---
    
    /**
     * Импорт из CSV
     */
    private function importFromCSV($file, $skipDuplicates, $assignDefaultRole)
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;
        
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle, 1000, ',');
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $line++;
            
            if (count($data) < 2) {
                $errors[] = "Строка {$line}: Недостаточно данных";
                continue;
            }
            
            $email = trim($data[0] ?? '');
            $username = trim($data[1] ?? '');
            $fullname = trim($data[2] ?? '');
            
            if (empty($email) || empty($username)) {
                $errors[] = "Строка {$line}: Отсутствует email или имя пользователя";
                continue;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Строка {$line}: Некорректный email адрес: {$email}";
                continue;
            }
            
            if ($skipDuplicates) {
                $exists = DB::table('users')->where('email', $email)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }
            
            try {
                DB::beginTransaction();
                
                // Создаем пользователя
                $userId = DB::table('users')->insertGetId([
                    'username' => $username,
                    'email' => $email,
                    'fullname' => $fullname ?: null,
                    'password' => Hash::make(uniqid()),
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Назначаем роль по умолчанию
                if ($assignDefaultRole) {
                    $userRole = DB::table('user_roles')->where('role_name', 'user')->first();
                    if ($userRole) {
                        DB::table('user_role_assignments')->insert([
                            'user_id' => $userId,
                            'role_id' => $userRole->role_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                DB::commit();
                $imported++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Строка {$line}: Ошибка базы данных - " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
    
    /**
     * Импорт из Excel (простая реализация)
     */
    private function importFromExcel($file, $skipDuplicates, $assignDefaultRole)
    {
        
        $tmpPath = $file->getPathname();
        $extension = $file->getClientOriginalExtension();
        
        if ($extension === 'xlsx' || $extension === 'xls') {
            // Конвертируем в CSV для простоты
            throw new \Exception('Импорт из Excel файлов требует установки дополнительных библиотек');
        }
        
        return $this->importFromCSV($file, $skipDuplicates, $assignDefaultRole);
    }
    
    public function exportUsers(Request $request)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $query = DB::table('users as u')
            ->select(
                'u.user_id',
                'u.username',
                'u.email',
                'u.fullname',
                'u.created_at',
                DB::raw('(SELECT COUNT(*) FROM fields WHERE user_id = u.user_id) as fields_count')
            );
        
        // Применяем текущие фильтры из запроса
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.username', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('u.fullname', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('role') && $request->role) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('user_role_assignments')
                  ->whereRaw('user_role_assignments.user_id = u.user_id')
                  ->where('user_role_assignments.role_id', $request->role);
            });
        }
        
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('u.is_active', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('u.is_active', 0);
            }
        }
        
        // Дополнительные фильтры из панели экспорта
        if ($request->has('filters')) {
            $filters = (array) $request->input('filters');
            
            if (in_array('with_fields', $filters)) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('fields')
                      ->whereRaw('fields.user_id = u.user_id');
                });
            }
            
            if (in_array('active_only', $filters)) {
                $query->where('u.is_active', 1);
            }
        }
        
        $users = $query->get();
        
        // Выбранные колонки
        $columns = $request->input('columns', ['id', 'username', 'email', 'created_at', 'fields_count', 'total_area', 'field_details']);
        $format = $request->input('format', 'csv');
        
        // Подготовка данных для экспорта
        $exportData = [];
        foreach ($users as $user) {
            $row = [];
            
            // Получаем поля пользователя
            $fields = DB::table('fields')
                ->where('user_id', $user->user_id)
                ->select('field_name', 'field_area')
                ->get();
            
            $totalArea = $fields->sum('field_area');
            $fieldDetails = '';
            
            foreach ($fields as $field) {
                $fieldDetails .= $field->field_name . ': ' . number_format($field->field_area, 2) . ' га; ';
            }
            
            $fieldDetails = rtrim($fieldDetails, '; ');
            
            if (in_array('id', $columns)) {
                $row['ID пользователя'] = $user->user_id;
            }
            
            if (in_array('username', $columns)) {
                $row['Имя пользователя'] = $user->username;
            }
            
            if (in_array('email', $columns)) {
                $row['Email'] = $user->email;
            }
            
            if (in_array('fullname', $columns)) {
                $row['Полное имя'] = $user->fullname ?? '-';
            }
            
            if (in_array('created_at', $columns)) {
                $row['Дата регистрации'] = date('d.m.Y H:i', strtotime($user->created_at));
            }
            
            if (in_array('fields_count', $columns)) {
                $row['Количество полей'] = $fields->count();
            }
            
            if (in_array('total_area', $columns)) {
                $row['Общая площадь (га)'] = number_format($totalArea, 2);
            }
            
            if (in_array('field_details', $columns)) {
                $row['Детали полей'] = $fieldDetails ?: 'Нет полей';
            }
            
            $exportData[] = $row;
        }
        
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        
        if ($format === 'excel') {
            return $this->exportToExcel($exportData, $filename);
        } elseif ($format === 'pdf') {
            return $this->exportToPDF($exportData, $filename);
        } else {
            return $this->exportToCSV($exportData, $filename);
        }
    }
    
    /**
     * Экспорт в CSV
     */
    private function exportToCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM для корректного отображения кириллицы
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
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
    
    /**
     * Экспорт в Excel (табулированный текст)
     */
    private function exportToExcel($data, $filename)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
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
    
    /**
     * Экспорт в PDF (HTML с указанием типа PDF)
     */
    private function exportToPDF($data, $filename)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Экспорт пользователей</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #47866A; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                h1 { color: #47866A; }
            </style>
        </head>
        <body>
            <h1>Экспорт пользователей</h1>
            <p>Дата экспорта: ' . date('d.m.Y H:i') . '</p>';
        
        if (!empty($data)) {
            $html .= '<table>';
            
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
            
            $html .= '</table>';
        }
        
        $html .= '</body></html>';
        
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        // возвращаем HTML с указанием типа PDF
        return response($html, 200, $headers);
    }
    // Создать новую роль
    public function storeRole(Request $request)
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
    
    // Обновить роль (метод PUT/PATCH)
    public function updateRole(Request $request, $roleId)
    {
        if (!php_auth_check() || !php_auth_is_admin()) {
            abort(403, 'Доступ запрещен.');
        }
        
        $validated = $request->validate([
            'role_name' => 'required|string|max:50|unique:user_roles,role_name,' . $roleId . ',role_id',
        ]);
        
        // Нельзя изменять системные роли
        $systemRoles = ['admin', 'user'];
        $role = DB::table('user_roles')->where('role_id', $roleId)->first();
        
        if ($role && in_array($role->role_name, $systemRoles)) {
            return redirect()->back()->with('error', 'Нельзя изменить системную роль');
        }
        
        DB::table('user_roles')->where('role_id', $roleId)->update([
            'role_name' => $validated['role_name'],
            'updated_at' => now(),
        ]);
        
        return redirect()->route('admin.roles')->with('success', 'Роль обновлена');
    }

/**
 * Получить информацию о пользователе для AJAX
 */
public function getUserInfo($userId)
{
    if (!php_auth_check() || !php_auth_is_admin()) {
        abort(403, 'Доступ запрещен.');
    }
    
    $user = DB::table('users')->where('user_id', $userId)->first();
    
    if (!$user) {
        return response('<div class="alert alert-danger">Пользователь не найден</div>');
    }
    
    // Получаем роли пользователя
    $roles = DB::table('user_role_assignments as ura')
        ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
        ->where('ura.user_id', $userId)
        ->pluck('ur.role_name')
        ->toArray();
    
    // Получаем поля пользователя
    $fields = DB::table('fields')
        ->where('user_id', $userId)
        ->select('field_id', 'field_name', 'field_area')
        ->get();
    
    $fieldCount = $fields->count();
    $totalArea = $fields->sum('field_area');
    
    $html = '
    <div class="user-info-content">
        <div class="user-avatar">
            ' . ($user->avatar_url ? 
                '<img src="' . $user->avatar_url . '" alt="' . $user->username . '" class="user-avatar-img">' : 
                '<div class="user-avatar-default">' . strtoupper(substr($user->username, 0, 2)) . '</div>'
            ) . '
        </div>
        
        <div class="user-basic-info">
            <h4 class="user-name">' . htmlspecialchars($user->username) . '</h4>
            <p class="user-email">' . htmlspecialchars($user->email) . '</p>
            <span class="user-id-badge">ID: ' . $user->user_id . '</span>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-user-tag"></i>
                    Полное имя
                </div>
                <div class="info-value">' . ($user->fullname ? htmlspecialchars($user->fullname) : 'Не указано') . '</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-calendar-alt"></i>
                    Дата регистрации
                </div>
                <div class="info-value">' . date('d.m.Y H:i:s', strtotime($user->created_at)) . '</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-shield-alt"></i>
                    Роли
                </div>
                <div class="info-value">
                    <div class="roles-tags">';
    
    if (!empty($roles)) {
        foreach ($roles as $role) {
            $roleClass = $role == 'admin' ? 'admin' : ($role == 'moderator' ? 'moderator' : 'user');
            $html .= '<span class="role-tag ' . $roleClass . '">' . $role . '</span>';
        }
    } else {
        $html .= '<span class="text-muted">Нет ролей</span>';
    }
    
    $html .= '
                    </div>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-map-marked-alt"></i>
                    Количество полей
                </div>
                <div class="info-value">' . $fieldCount . '</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-ruler-combined"></i>
                    Общая площадь
                </div>
                <div class="info-value">' . number_format($totalArea, 2) . ' га</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-list"></i>
                    Список полей
                </div>
                <div class="info-value">';
    
    if ($fields->count() > 0) {
        $html .= '<div class="fields-list">';
        foreach ($fields as $field) {
            $html .= '
                <div class="field-item">
                    <a href="/field/' . $field->field_id . '" class="field-link" target="_blank" title="Открыть поле">
                        <span class="field-name">' . htmlspecialchars($field->field_name) . '</span>
                        <span class="field-area">' . number_format($field->field_area, 2) . ' га</span>
                    </a>
                </div>';
        }
        $html .= '</div>
                <div class="view-all-fields">
                    <button onclick="viewUserFields(' . $userId . ')" class="view-fields-btn">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Просмотреть все поля в панели админа
                    </button>
                </div>';
    } else {
        $html .= '<div class="no-fields">Нет полей</div>';
    }
    
    $html .= '
                </div>
            </div>
        </div>
    </div>';
    
    return response($html);
}
}