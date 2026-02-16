@extends('sample.main')

@section('header-title')
Управление пользователями
@endsection

@section('content')
<!-- Добавляем Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-container">
    <!-- Заголовок и навигация -->
    <div class="roles-header">
        <h1><i class="fas fa-users me-2"></i>Управление пользователями</h1>
        <div class="roles-nav">
            <a href="{{ route('admin.dashboard') }}">📊 Дашборд</a>
            <a href="{{ route('admin.users') }}" class="nav-active">👥 Пользователи</a>
            <a href="{{ route('admin.fields') }}">📍 Поля</a>
            <a href="{{ route('admin.roles') }}">🛡️ Роли</a>
            <a href="/" class="nav-exit">← На сайт</a>
        </div>
    </div>
    
    <div class="admin-stats">
        <div class="stat-item">
            <div class="stat-icon stat-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $users->total() }}</h3>
                <p>Всего пользователей</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon stat-info">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-content">
                <h3>
                    @php
                        $adminCount = DB::table('user_role_assignments as ura')
                            ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                            ->where('ur.role_name', 'admin')
                            ->distinct('ura.user_id')
                            ->count('ura.user_id');
                        echo $adminCount;
                    @endphp
                </h3>
                <p>Администраторов</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon stat-success">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <div class="stat-content">
                <h3>
                    @php
                        $usersWithFields = DB::table('users as u')
                            ->join('fields as f', 'u.user_id', '=', 'f.user_id')
                            ->distinct('u.user_id')
                            ->count('u.user_id');
                        echo $usersWithFields;
                    @endphp
                </h3>
                <p>С полями</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon stat-warning">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <h3>
                    @php
                        $recentUsers = DB::table('users')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->count();
                        echo $recentUsers;
                    @endphp
                </h3>
                <p>За неделю</p>
            </div>
        </div>
    </div>
    
    <!-- Панель поиска и фильтров -->
    <div class="search-panel">
        <form method="GET" action="{{ route('admin.users') }}">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Поиск по имени, email..."
                       value="{{ request('search') }}">
                @if(request('search'))
                <a href="{{ route('admin.users') }}" class="search-clear">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
            
            <div class="filter-group">
                <select name="role" class="filter-select">
                    <option value="">Все роли</option>
                    @foreach($availableRoles as $role)
                        <option value="{{ $role->role_id }}" 
                                {{ request('role') == $role->role_id ? 'selected' : '' }}>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
                
                <select name="status" class="filter-select">
                    <option value="">Все статусы</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                </select>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search me-1"></i> Применить
                </button>
                
                <!-- Кнопка экспорта -->
                <button type="button" class="export-btn" onclick="toggleExportPanel()">
                    <i class="fas fa-file-export me-1"></i> Экспорт
                </button>
            </div>
        </form>
    </div>
    
    <!-- Панель экспорта -->
    <div class="export-panel" id="exportPanel" style="display: none;">
        <div class="export-header">
            <h4><i class="fas fa-file-export me-2"></i>Экспорт данных пользователей</h4>
            <button type="button" class="close-panel-btn" onclick="toggleExportPanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="GET" action="{{ route('admin.users.export') }}" class="export-form">
            <div class="export-options">
                <div class="export-section">
                    <label class="export-label">
                        <i class="fas fa-file-alt me-2"></i>Формат экспорта
                    </label>
                    <div class="format-options">
                        <label class="format-option">
                            <input type="radio" name="format" value="csv" checked>
                            <span class="format-label">
                                <i class="fas fa-file-csv"></i>
                                <span>CSV</span>
                            </span>
                        </label>
                        <label class="format-option">
                            <input type="radio" name="format" value="excel">
                            <span class="format-label">
                                <i class="fas fa-file-excel"></i>
                                <span>Excel</span>
                            </span>
                        </label>
                        <label class="format-option">
                            <input type="radio" name="format" value="pdf">
                            <span class="format-label">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                            </span>
                        </label>
                    </div>
                </div>
                
                <div class="export-section">
                    <label class="export-label">
                        <i class="fas fa-columns me-2"></i>Выберите колонки для экспорта
                    </label>
                    <div class="columns-grid">
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="id" checked>
                            <span>ID пользователя</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="username" checked>
                            <span>Имя пользователя</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="email" checked>
                            <span>Email</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="fullname">
                            <span>Полное имя</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="created_at" checked>
                            <span>Дата регистрации</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="fields_count" checked>
                            <span>Количество полей</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="total_area" checked>
                            <span>Общая площадь (га)</span>
                        </label>
                        <label class="column-option">
                            <input type="checkbox" name="columns[]" value="field_details">
                            <span>Детали полей (название + га)</span>
                        </label>
                    </div>
                </div>
                
                <div class="export-section">
                    <label class="export-label">
                        <i class="fas fa-filter me-2"></i>Дополнительные фильтры
                    </label>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" name="filters[]" value="with_fields">
                            <span>Только пользователи с полями</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="filters[]" value="active_only">
                            <span>Только активные пользователи</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="export-actions">
                <button type="button" class="cancel-btn" onclick="toggleExportPanel()">
                    <i class="fas fa-times me-1"></i> Отмена
                </button>
                <button type="submit" class="confirm-export-btn">
                    <i class="fas fa-download me-1"></i> Экспортировать
                </button>
            </div>
        </form>
    </div>
    
    <!-- Таблица пользователей -->
    <div class="roles-table-container">
        <div class="table-header">
            <h2>Список пользователей</h2>
            <div class="table-actions">
                <button class="btn-refresh" onclick="window.location.reload()" title="Обновить">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            {{ session('error') }}
        </div>
        @endif
        
        @if(session('import_success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('import_success') }}
        </div>
        @endif
        
        @if(session('import_errors'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Импорт завершен с ошибками:
            <ul class="mt-2">
                @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            Ошибки при импорте:
            <ul class="mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <table class="roles-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-user">Пользователь</th>
                    <th class="col-email">Email</th>
                    <th class="col-roles">Роли</th>
                    <th class="col-date">Дата регистрации</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="col-id">
                        <span class="user-id">#{{ $user->user_id }}</span>
                    </td>
                    <td class="col-user">
                        <div class="role-info">
                            <div class="role-icon">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->username }}" 
                                         class="avatar-img">
                                @else
                                    <div class="avatar-initials">
                                        {{ strtoupper(substr($user->username, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="role-name">{{ $user->username }}</div>
                                @if($user->fullname)
                                    <div class="user-fullname">{{ $user->fullname }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="col-email">
                        <div class="email-info">
                            <i class="fas fa-envelope email-icon"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                    </td>
                    <td class="col-roles">
                        <div class="roles-list">
                            @if(!empty($user->roles) && is_array($user->roles))
                                @php
                                    $roles = $user->roles;
                                    $roleIds = $user->role_ids ?? [];
                                @endphp
                                @foreach($roles as $index => $role)
                                <div class="role-badge {{ $role == 'admin' ? 'role-admin' : ($role == 'moderator' ? 'role-moderator' : 'role-user') }}">
                                    {{ $role }}
                                    @if($role != 'user' && isset($roleIds[$index]))
                                    <a href="{{ route('admin.users.removeRole', ['user' => $user->user_id, 'role' => $roleIds[$index]]) }}"
                                       class="role-remove"
                                       onclick="return confirm('Удалить роль {{ $role }} у пользователя?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                                @endforeach
                            @else
                                <span class="no-roles">Без роли</span>
                            @endif
                        </div>
                        
                        <form method="POST" 
                              action="{{ route('admin.users.assignRole', $user->user_id) }}"
                              class="role-assign-form">
                            @csrf
                            <div class="role-select-container">
                                <select name="role_id" class="role-select" required>
                                    <option value="">Добавить роль</option>
                                    @foreach($availableRoles as $role)
                                        @php
                                            $userRoles = is_array($user->roles) ? $user->roles : [];
                                        @endphp
                                        @if(!in_array($role->role_name, $userRoles))
                                            <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="role-add-btn" title="Добавить роль">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </form>
                    </td>
                    <td class="col-date">
                        <span class="role-date">{{ date('d.m.Y', strtotime($user->created_at)) }}</span>
                    </td>
                    <td class="col-actions">
                        <div class="action-buttons">
                            <button type="button" 
                                    class="action-btn edit-btn"
                                    onclick="showUserInfo({{ $user->user_id }})"
                                    title="Просмотр профиля">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Кнопки управления пользователями -->
                            @if($user->user_id != session('user_id'))
                                @if($user->is_active ?? true)
                                <form method="POST" 
                                      action="{{ route('admin.users.deactivate', $user->user_id) }}"
                                      class="action-form d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn deactivate-btn" title="Деактивировать">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" 
                                      action="{{ route('admin.users.activate', $user->user_id) }}"
                                      class="action-form d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn activate-btn" title="Активировать">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </form>
                                @endif
                                
                                <form method="POST" 
                                      action="{{ route('admin.users.delete', $user->user_id) }}"
                                      class="action-form d-inline"
                                      onsubmit="return confirm('Удалить пользователя {{ $user->username }}? Это действие нельзя отменить.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete-btn" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($users->hasPages())
        <div class="pagination-wrapper">
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

<!-- Всплывающая форма с информацией о пользователе -->
<div id="userInfoPopup" class="popup-form">
    <div class="popup-overlay" onclick="closeUserInfo()"></div>
    <div class="popup-content">
        <div class="popup-header">
            <h3><i class="fas fa-user-circle me-2"></i>Информация о пользователе</h3>
            <button type="button" class="popup-close" onclick="closeUserInfo()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body" id="userInfoContent">
            <!-- Контент будет загружен через JavaScript -->
            <div class="loading-info">
                <div class="spinner"></div>
                <p>Загрузка информации...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Основные стили */
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Заголовок и навигация */
.roles-header {
    margin-bottom: 40px;
}

.roles-header h1 {
    color: #47866A;
    margin: 0 0 20px 0;
    font-size: 32px;
    display: flex;
    align-items: center;
}

.roles-nav {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.roles-nav a {
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
    background: #f8f9fa;
    color: #666;
}

.roles-nav a:hover {
    background: #e9ecef;
    color: #333;
}

.nav-active {
    background: linear-gradient(90deg, #47866A, #5CA08A) !important;
    color: white !important;
}

.nav-exit {
    margin-left: auto;
    background: #6c757d !important;
    color: white !important;
}

.nav-exit:hover {
    background: #5a6268 !important;
}

/* Статистика */
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-primary {
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-info {
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.stat-success {
    background: linear-gradient(90deg, #10b981, #059669);
}

.stat-warning {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.stat-content h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

/* Панель поиска */
.search-panel {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.search-box {
    position: relative;
    margin-bottom: 20px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
}

.search-input {
    width: 100%;
    padding: 12px 20px 12px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    background: #f8f9fa;
}

.search-input:focus {
    outline: none;
    border-color: #47866A;
    background: white;
}

.search-clear {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    background: #e0e0e0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
}

.search-clear:hover {
    background: #d0d0d0;
}

.filter-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-select {
    flex: 1;
    min-width: 200px;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    font-size: 14px;
    color: #333;
}

.filter-btn {
    padding: 10px 25px;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.filter-btn:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

/* Панель экспорта */
.export-panel {
    background: white;
    border-radius: 12px;
    padding: 0;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.export-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(90deg, #f8fafc 0%, #e2e8f0 100%);
    border-bottom: 1px solid #e2e8f0;
}

.export-header h4 {
    margin: 0;
    color: #475569;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.close-panel-btn {
    background: white;
    border: 2px solid #cbd5e1;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s;
}

.close-panel-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.export-form {
    padding: 25px;
}

.export-options {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.export-section {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.export-label {
    display: flex;
    align-items: center;
    color: #475569;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 15px;
}

.export-label i {
    color: #47866A;
}

.format-options {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.format-option {
    cursor: pointer;
}

.format-option input[type="radio"] {
    display: none;
}

.format-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px 20px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    min-width: 80px;
    transition: all 0.3s;
}

.format-label i {
    font-size: 24px;
    color: #94a3b8;
}

.format-label span {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.format-option input:checked + .format-label {
    border-color: #47866A;
    background: rgba(71, 134, 106, 0.05);
}

.format-option input:checked + .format-label i,
.format-option input:checked + .format-label span {
    color: #47866A;
}

.columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}

.column-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.column-option:hover {
    border-color: #47866A;
}

.column-option input[type="checkbox"] {
    margin: 0;
}

.column-option span {
    font-size: 14px;
    color: #475569;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-option:hover {
    border-color: #47866A;
}

.filter-option input[type="checkbox"] {
    margin: 0;
}

.filter-option span {
    font-size: 14px;
    color: #475569;
}

.export-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.cancel-btn {
    padding: 12px 25px;
    background: #f8f9fa;
    color: #666;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.cancel-btn:hover {
    background: #e9ecef;
    color: #333;
}

.confirm-export-btn {
    padding: 12px 30px;
    background: linear-gradient(90deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.confirm-export-btn:hover {
    background: linear-gradient(90deg, #0da271, #047852);
    transform: translateY(-2px);
}

/* Кнопка экспорта в фильтрах */
.export-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
    background: linear-gradient(90deg, #f59e0b, #d97706);
    color: white;
}

.export-btn:hover {
    background: linear-gradient(90deg, #d97706, #b45309);
    transform: translateY(-2px);
}

/* Таблица */
.roles-table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h2 {
    margin: 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.roles-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.roles-table thead {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.roles-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #555;
    font-size: 13px;
    text-transform: uppercase;
}

.roles-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.roles-table tbody tr:hover {
    background: #f8fafc;
}

/* Стили для колонок */
.col-id {
    width: 60px;
}

.user-id {
    color: #666;
    font-weight: 500;
    font-size: 13px;
}

.col-user {
    min-width: 200px;
}

.role-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.role-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.role-name {
    font-weight: 600;
    color: #333;
    font-size: 15px;
    margin-bottom: 4px;
}

.user-fullname {
    color: #666;
    font-size: 13px;
}

.col-email {
    min-width: 200px;
}

.email-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.email-icon {
    color: #666;
    font-size: 14px;
}

.col-roles {
    min-width: 200px;
}

.roles-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.role-admin {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.role-moderator {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.role-user {
    background: linear-gradient(90deg, #10b981, #059669);
}

.role-remove {
    color: white;
    opacity: 0.7;
    text-decoration: none;
    font-size: 10px;
    margin-left: 3px;
}

.role-remove:hover {
    opacity: 1;
}

.no-roles {
    color: #999;
    font-style: italic;
    font-size: 13px;
}

.role-assign-form {
    margin-top: 10px;
}

.role-select-container {
    display: flex;
    gap: 5px;
}

.role-select {
    flex: 1;
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    background: #f8f9fa;
}

.role-add-btn {
    padding: 6px 12px;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.role-add-btn:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
}

.col-date {
    width: 120px;
}

.role-date {
    color: #666;
    font-size: 13px;
    font-weight: 500;
}

/* Кнопки действий */
.col-actions {
    width: 100px;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    flex-shrink: 0;
}

.edit-btn {
    background: #47866A;
    color: white;
    border: none;
}
.edit-btn:hover {
    background: #3a7557;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Кнопки управления пользователями */
.activate-btn {
    background: #10b981;
    color: white;
    border: none;
}

.activate-btn:hover {
    background: #0da271;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.deactivate-btn {
    background: #f59e0b;
    color: white;
    border: none;
}

.deactivate-btn:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.delete-btn {
    background: #dc3545;
    color: white;
    border: none;
}

.delete-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.action-form {
    display: inline;
}

/* Аллерты */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
}

.alert-success {
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    border: 1px solid #10b981;
    color: #065f46;
}

.alert-warning {
    background: linear-gradient(90deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
    border: 1px solid #f59e0b;
    color: #854d0e;
}

.alert-danger {
    background: linear-gradient(90deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
    border: 1px solid #ef4444;
    color: #991b1b;
}

.btn-refresh {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.btn-refresh:hover {
    background: #e9ecef;
    color: #333;
    transform: rotate(90deg);
}

/* Пагинация */
.pagination-wrapper {
    padding: 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: center;
}

.pagination-wrapper nav {
    display: inline-flex;
}

/* Стили для всплывающей формы */
.popup-form {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.popup-form.active {
    display: flex;
}

.popup-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
}

.popup-content {
    position: relative;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    z-index: 1001;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
}

.popup-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.popup-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.popup-body {
    padding: 20px;
    max-height: calc(80vh - 70px);
    overflow-y: auto;
}

/* Стили для информации о пользователе */
.user-info-content {
    font-size: 14px;
}

.user-avatar {
    text-align: center;
    margin-bottom: 20px;
}

.user-avatar-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f0f0f0;
    margin: 0 auto 10px;
}

.user-avatar-default {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 24px;
    margin: 0 auto 10px;
    border: 4px solid #f0f0f0;
}

.user-basic-info {
    margin-bottom: 20px;
}

.user-name {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    text-align: center;
}

.user-email {
    color: #666;
    text-align: center;
    margin-bottom: 10px;
}

.user-id-badge {
    display: inline-block;
    background: #f0f0f0;
    color: #666;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    margin: 0 auto;
    text-align: center;
    display: block;
    width: fit-content;
}

.info-grid {
    display: grid;
    gap: 15px;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.info-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-value {
    color: #333;
    font-size: 14px;
}

.roles-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}

.role-tag {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.role-tag.admin {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.role-tag.moderator {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.role-tag.user {
    background: linear-gradient(90deg, #10b981, #059669);
}

.fields-list {
    margin-top: 10px;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 8px;
}

.field-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s;
}

.field-item:hover {
    background: #f8f9fa;
}

.field-item:last-child {
    border-bottom: none;
}

.field-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    text-decoration: none;
    color: inherit;
}

.field-name {
    font-weight: 500;
    color: #555;
    flex: 1;
}

.field-area {
    color: #666;
    font-size: 13px;
    background: #f0f0f0;
    padding: 2px 8px;
    border-radius: 4px;
    margin-left: 10px;
}

.field-link:hover .field-name {
    color: #47866A;
}

.field-link:hover .field-area {
    background: #47866A;
    color: white;
}

.no-fields {
    color: #999;
    font-style: italic;
    text-align: center;
    padding: 10px;
}

.view-all-fields {
    margin-top: 10px;
    text-align: center;
}

.view-fields-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
}

.view-fields-btn:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.loading-info {
    text-align: center;
    padding: 40px 20px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f0f0f0;
    border-top-color: #47866A;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* АДАПТИВНОСТЬ */
@media (max-width: 1200px) {
    .admin-container {
        padding: 20px 15px;
    }
    
    .roles-nav {
        gap: 10px;
    }
    
    .roles-nav a {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .admin-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .roles-header h1 {
        font-size: 28px;
    }
    
    .filter-group {
        flex-direction: column;
    }
    
    .filter-select,
    .filter-btn,
    .export-btn {
        width: 100%;
    }
    
    .export-panel {
        margin: 0 -15px 20px;
        border-radius: 0;
    }
}

@media (max-width: 768px) {
    .admin-container {
        padding: 15px 10px;
    }
    
    .roles-header {
        margin-bottom: 25px;
    }
    
    .roles-header h1 {
        font-size: 24px;
        margin-bottom: 15px;
    }
    
    .roles-nav {
        flex-direction: column;
        gap: 8px;
    }
    
    .roles-nav a {
        width: 100%;
        text-align: center;
    }
    
    .nav-exit {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .admin-stats {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .stat-item {
        padding: 15px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-content h3 {
        font-size: 24px;
    }
    
    .search-panel {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .search-box {
        margin-bottom: 15px;
    }
    
    .search-input {
        padding: 10px 15px 10px 40px;
        font-size: 14px;
    }
    
    .columns-grid {
        grid-template-columns: 1fr;
    }
    
    .format-options {
        justify-content: center;
    }
    
    .export-actions {
        flex-direction: column;
    }
    
    .cancel-btn, .confirm-export-btn {
        width: 100%;
        justify-content: center;
    }
    
    .roles-table-container {
        padding: 15px;
        overflow-x: auto;
    }
    
    .roles-table {
        font-size: 12px;
        min-width: 800px;
    }
    
    .roles-table th,
    .roles-table td {
        padding: 10px 8px;
    }
    
    .role-info {
        gap: 8px;
    }
    
    .role-icon {
        width: 32px;
        height: 32px;
    }
    
    .avatar-initials {
        font-size: 14px;
    }
    
    .role-name {
        font-size: 13px;
    }
    
    .user-fullname {
        font-size: 11px;
    }
    
    .email-icon {
        font-size: 12px;
    }
    
    .role-badge {
        padding: 3px 8px;
        font-size: 11px;
    }
    
    .action-buttons {
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }
    
    .popup-content {
        width: 95%;
        max-width: 95%;
    }
    
    .view-fields-btn {
        padding: 6px 12px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .admin-container {
        padding: 10px 5px;
    }
    
    .roles-header h1 {
        font-size: 20px;
    }
    
    .stat-content h3 {
        font-size: 20px;
    }
    
    .stat-content p {
        font-size: 12px;
    }
    
    .export-header {
        padding: 15px;
    }
    
    .export-header h4 {
        font-size: 16px;
    }
    
    .export-form {
        padding: 15px;
    }
    
    .export-section {
        padding: 15px;
    }
    
    .format-label {
        padding: 10px 15px;
        min-width: 70px;
    }
    
    .format-label i {
        font-size: 20px;
    }
    
    .format-label span {
        font-size: 12px;
    }
    
    .column-option {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .filter-option {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .roles-table {
        font-size: 11px;
    }
    
    .roles-table th,
    .roles-table td {
        padding: 8px 6px;
    }
    
    .role-name {
        font-size: 12px;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .popup-header {
        padding: 15px;
    }
    
    .popup-header h3 {
        font-size: 16px;
    }
    
    .popup-body {
        padding: 15px;
    }
    
    .user-name {
        font-size: 18px;
    }
    
    .info-item {
        padding: 12px;
    }
}
</style>

<script>
// Функции для управления панелями экспорта
function toggleExportPanel() {
    const exportPanel = document.getElementById('exportPanel');
    
    if (exportPanel.style.display === 'none' || exportPanel.style.display === '') {
        exportPanel.style.display = 'block';
    } else {
        exportPanel.style.display = 'none';
    }
}

// Показать информацию о пользователе
function showUserInfo(userId) {
    const popup = document.getElementById('userInfoPopup');
    const content = document.getElementById('userInfoContent');
    
    // Показываем попап с загрузкой
    popup.classList.add('active');
    content.innerHTML = `
        <div class="loading-info">
            <div class="spinner"></div>
            <p>Загрузка информации...</p>
        </div>
    `;
    
    // Загружаем данные через AJAX
    fetch(`/admin/users/${userId}/info`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка загрузки данных');
            }
            return response.text();
        })
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Ошибка загрузки данных: ${error.message}
                </div>
            `;
        });
}

// Закрыть информацию о пользователе
function closeUserInfo() {
    document.getElementById('userInfoPopup').classList.remove('active');
}

// Перейти к полям пользователя
function viewUserFields(userId) {
    // Закрываем попап
    closeUserInfo();
    
    // Перенаправляем на страницу полей с фильтром по пользователю
    window.location.href = `/admin/fields?user=${userId}`;
}

// Закрытие по нажатию ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserInfo();
    }
});

// Управление выбором всех колонок
document.addEventListener('DOMContentLoaded', function() {
    // Выбор всех колонок в экспорте
    const columnsGrid = document.querySelector('.columns-grid');
    if (columnsGrid) {
        const selectAllCheckbox = document.createElement('label');
        selectAllCheckbox.className = 'column-option';
        selectAllCheckbox.innerHTML = `
            <input type="checkbox" id="selectAllColumns">
            <span>Выбрать все</span>
        `;
        
        columnsGrid.parentNode.insertBefore(selectAllCheckbox, columnsGrid);
        
        const selectAll = document.getElementById('selectAllColumns');
        const columnCheckboxes = document.querySelectorAll('.columns-grid input[type="checkbox"]');
        
        selectAll.addEventListener('change', function() {
            columnCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        columnCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(columnCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(columnCheckboxes).some(cb => cb.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = anyChecked && !allChecked;
            });
        });
    }
    
    // Авто-поиск при изменении фильтров
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
    
    // Анимация для статистики
    const statItems = document.querySelectorAll('.stat-item');
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Закрытие попапа при клике на оверлей
    document.querySelector('.popup-overlay').addEventListener('click', closeUserInfo);
});
</script>
@endsection