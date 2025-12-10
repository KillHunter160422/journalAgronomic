@extends('sample.main')

@section('content')
<div class="admin-container">
    <!-- Заголовок и навигация -->
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="admin-title">
                    <i class="fas fa-users me-2"></i>Управление пользователями
                </h1>
                <p class="admin-subtitle">Всего пользователей: <span class="badge-count">{{ $users->total() }}</span></p>
            </div>
            <div class="admin-actions">
                <a href="/" class="btn-back">
                    <i class="fas fa-arrow-left me-1"></i> На главную
                </a>
            </div>
        </div>
        
        <div class="admin-navigation">
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <i class="fas fa-tachometer-alt me-2"></i> Дашборд
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item active">
                <i class="fas fa-users me-2"></i> Пользователи
            </a>
            <a href="{{ route('admin.fields') }}" class="nav-item">
                <i class="fas fa-map-marked-alt me-2"></i> Поля
            </a>
            <a href="{{ route('admin.roles') }}" class="nav-item">
                <i class="fas fa-user-shield me-2"></i> Роли
            </a>
        </div>
    </div>
    
    <!-- Поиск -->
    <div class="search-section">
        <form method="GET" action="{{ route('admin.users') }}">
            <div class="search-wrapper">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
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
                
                <div class="search-filters">
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
                    
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search me-1"></i> Найти
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Экспорт -->
    <div class="export-section">
        <div class="export-header">
            <h4><i class="fas fa-file-export me-2"></i>Экспорт данных</h4>
            <button class="export-toggle-btn" onclick="toggleExportOptions()">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="export-options" id="exportOptions" style="display: none;">
            <form method="GET" action="{{ route('admin.users.export') }}">
                <div class="export-grid">
                    <div class="export-group">
                        <label class="export-label">
                            <i class="fas fa-file-alt me-2"></i>Формат
                        </label>
                        <div class="format-buttons">
                            <label class="format-option active">
                                <input type="radio" name="format" value="csv" checked>
                                <div class="format-content">
                                    <i class="fas fa-file-csv"></i>
                                    <span>CSV</span>
                                </div>
                            </label>
                            <label class="format-option">
                                <input type="radio" name="format" value="excel">
                                <div class="format-content">
                                    <i class="fas fa-file-excel"></i>
                                    <span>Excel</span>
                                </div>
                            </label>
                            <label class="format-option">
                                <input type="radio" name="format" value="pdf">
                                <div class="format-content">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>PDF</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="export-group">
                        <label class="export-label">
                            <i class="fas fa-columns me-2"></i>Колонки
                        </label>
                        <div class="columns-grid">
                            <label class="column-option">
                                <input type="checkbox" name="columns[]" value="id" checked>
                                <span>ID</span>
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
                                <input type="checkbox" name="columns[]" value="roles">
                                <span>Роли</span>
                            </label>
                            <label class="column-option">
                                <input type="checkbox" name="columns[]" value="created_at" checked>
                                <span>Дата регистрации</span>
                            </label>
                            <label class="column-option">
                                <input type="checkbox" name="columns[]" value="fields_count">
                                <span>Количество полей</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="export-group">
                        <label class="export-label">
                            <i class="fas fa-calendar-alt me-2"></i>Период
                        </label>
                        <div class="date-range">
                            <div class="date-input-group">
                                <i class="fas fa-calendar-start"></i>
                                <input type="date" name="start_date" class="date-input">
                                <span class="date-label">С</span>
                            </div>
                            <div class="date-input-group">
                                <i class="fas fa-calendar-end"></i>
                                <input type="date" name="end_date" class="date-input">
                                <span class="date-label">По</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="export-group">
                        <label class="export-label">
                            <i class="fas fa-filter me-2"></i>Фильтры
                        </label>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="checkbox" name="filters[]" value="with_fields">
                                <span>Только с полями</span>
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="filters[]" value="with_roles">
                                <span>Только с ролями</span>
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="filters[]" value="active_only">
                                <span>Только активные</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="export-actions">
                    <button type="button" class="btn-cancel" onclick="toggleExportOptions()">
                        <i class="fas fa-times me-2"></i>Отмена
                    </button>
                    <button type="submit" class="btn-export">
                        <i class="fas fa-download me-2"></i>Экспортировать
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
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
        
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-content">
                <h3>
                    @php
                        $moderatorCount = DB::table('user_role_assignments as ura')
                            ->join('user_roles as ur', 'ura.role_id', '=', 'ur.role_id')
                            ->where('ur.role_name', 'moderator')
                            ->distinct('ura.user_id')
                            ->count('ura.user_id');
                        echo $moderatorCount;
                    @endphp
                </h3>
                <p>Модераторов</p>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">
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
        
        <div class="stat-card stat-warning">
            <div class="stat-icon">
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
    
    <!-- Таблица пользователей -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="admin-table">
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
                            <span class="user-id">{{ $user->user_id }}</span>
                        </td>
                        <td class="col-user">
                            <div class="user-info">
                                <div class="user-avatar">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" 
                                             alt="{{ $user->username }}" 
                                             class="avatar-img">
                                    @else
                                        <div class="avatar-default">
                                            {{ strtoupper(substr($user->username, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $user->username }}</div>
                                    @if($user->fullname)
                                        <div class="user-fullname">{{ $user->fullname }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="col-email">
                            <div class="email-wrapper">
                                <i class="fas fa-envelope email-icon"></i>
                                <span class="email-text">{{ $user->email }}</span>
                            </div>
                        </td>
                        <td class="col-roles">
                            <div class="roles-container">
                                @if(!empty($user->roles) && is_array($user->roles))
                                    @php
                                        $roles = $user->roles;
                                        $roleIds = $user->role_ids ?? [];
                                    @endphp
                                    @foreach($roles as $index => $role)
                                    <div class="role-tag {{ $role == 'admin' ? 'role-admin' : ($role == 'moderator' ? 'role-moderator' : 'role-default') }}">
                                        <span class="role-name">{{ $role }}</span>
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
                                
                                <form method="POST" 
                                      action="{{ route('admin.users.assignRole', $user->user_id) }}"
                                      class="role-form">
                                    @csrf
                                    <div class="role-select-wrapper">
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
                            </div>
                        </td>
                        <td class="col-date">
                            <div class="date-wrapper">
                                <div class="date-day">{{ date('d.m.Y', strtotime($user->created_at)) }}</div>
                                <div class="date-time">{{ date('H:i', strtotime($user->created_at)) }}</div>
                            </div>
                        </td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <button type="button" 
                                        class="action-btn action-view"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewUserModal{{ $user->user_id }}"
                                        title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <form method="POST" 
                                      action="{{ route('admin.users.delete', $user->user_id) }}"
                                      class="action-form"
                                      onsubmit="return confirm('Удалить пользователя {{ $user->username }}? Это действие нельзя отменить.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-delete" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="pagination-wrapper">
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

<!-- Модальные окна -->
@foreach($users as $user)
<div class="modal fade" id="viewUserModal{{ $user->user_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle me-2"></i>Профиль пользователя: {{ $user->username }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="user-profile">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->username }}" 
                                     class="profile-avatar-img">
                            @else
                                <div class="profile-avatar-default">
                                    {{ strtoupper(substr($user->username, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="profile-info">
                            <h3 class="profile-name">{{ $user->username }}</h3>
                            <p class="profile-email">{{ $user->email }}</p>
                            <div class="profile-id">ID: {{ $user->user_id }}</div>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-user-tag me-2"></i>Полное имя
                            </div>
                            <div class="detail-value">{{ $user->fullname ?: 'Не указано' }}</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-calendar-alt me-2"></i>Дата регистрации
                            </div>
                            <div class="detail-value">{{ date('d.m.Y H:i:s', strtotime($user->created_at)) }}</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-shield-alt me-2"></i>Роли
                            </div>
                            <div class="detail-value">
                                @if(!empty($user->roles) && is_array($user->roles))
                                    @foreach($user->roles as $role)
                                    <span class="profile-role-tag {{ $role == 'admin' ? 'role-admin' : ($role == 'moderator' ? 'role-moderator' : 'role-default') }}">
                                        {{ $role }}
                                    </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Нет ролей</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-map-marked-alt me-2"></i>Количество полей
                            </div>
                            <div class="detail-value">
                                @php
                                    $fieldCount = DB::table('fields')->where('user_id', $user->user_id)->count();
                                    echo $fieldCount;
                                @endphp
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Закрыть
                </button>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Редактировать
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
/* Основные стили */
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Заголовок */
.admin-header {
    margin-bottom: 30px;
}

.admin-title {
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.admin-subtitle {
    color: #7f8c8d;
    font-size: 15px;
    margin: 8px 0 0 0;
}

.badge-count {
    background: #3498db;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.btn-back {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
}

/* Навигация */
.admin-navigation {
    display: flex;
    gap: 5px;
    background: white;
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-top: 25px;
}

.nav-item {
    flex: 1;
    padding: 14px 20px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 15px;
}

.nav-item:hover {
    background: #f8fafc;
    color: #334155;
    transform: translateY(-1px);
}

.nav-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Поиск */
.search-section {
    margin-bottom: 30px;
}

.search-wrapper {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.search-input-group {
    position: relative;
    margin-bottom: 20px;
}

.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
}

.search-input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s;
    background: #f8fafc;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-clear {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    background: #e2e8f0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.search-clear:hover {
    background: #cbd5e1;
    color: #475569;
}

.search-filters {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-select {
    flex: 1;
    min-width: 180px;
    padding: 12px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    font-size: 15px;
    color: #334155;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-btn {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    min-width: 140px;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

/* Экспорт */
.export-section {
    background: white;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.export-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-bottom: 1px solid #e2e8f0;
}

.export-header h4 {
    margin: 0;
    color: #475569;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.export-toggle-btn {
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

.export-toggle-btn:hover {
    border-color: #667eea;
    color: #667eea;
    transform: rotate(180deg);
}

.export-options {
    padding: 25px;
    border-top: 1px solid #f1f5f9;
}

.export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.export-group {
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
    color: #667eea;
}

.format-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.format-option {
    position: relative;
    cursor: pointer;
}

.format-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.format-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    min-width: 80px;
    transition: all 0.3s;
}

.format-content i {
    font-size: 24px;
    color: #94a3b8;
}

.format-content span {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.format-option input:checked + .format-content {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.format-option input:checked + .format-content i,
.format-option input:checked + .format-content span {
    color: #667eea;
}

.columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
}

.column-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.column-option:hover {
    border-color: #667eea;
    transform: translateY(-2px);
}

.column-option input[type="checkbox"] {
    margin: 0;
}

.column-option span {
    font-size: 14px;
    color: #475569;
}

.date-range {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.date-input-group {
    flex: 1;
    position: relative;
    min-width: 150px;
}

.date-input-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.date-input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.3s;
}

.date-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.date-label {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 13px;
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
    padding: 8px 12px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-option:hover {
    border-color: #667eea;
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
    border-top: 1px solid #f1f5f9;
}

.btn-cancel {
    padding: 12px 25px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.btn-cancel:hover {
    border-color: #cbd5e1;
    color: #475569;
    transform: translateY(-2px);
}

.btn-export {
    padding: 12px 30px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

/* Статистика */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-left: 5px solid;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-primary {
    border-left-color: #667eea;
}

.stat-info {
    border-left-color: #3b82f6;
}

.stat-success {
    border-left-color: #10b981;
}

.stat-warning {
    border-left-color: #f59e0b;
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

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-content h3 {
    margin: 0;
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

/* Таблица */
.table-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.admin-table th {
    padding: 20px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.admin-table td {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.admin-table tbody tr {
    transition: all 0.2s ease;
}

.admin-table tbody tr:hover {
    background: #f8fafc;
}

/* Стили ячеек */
.col-id {
    width: 80px;
}

.user-id {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    font-family: monospace;
}

.col-user {
    min-width: 250px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    flex-shrink: 0;
}

.avatar-img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.avatar-default {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    border: 3px solid white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 16px;
    margin-bottom: 4px;
}

.user-fullname {
    color: #64748b;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.col-email {
    min-width: 220px;
}

.email-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.email-icon {
    color: #94a3b8;
    font-size: 14px;
}

.email-text {
    color: #475569;
    font-size: 15px;
    word-break: break-all;
}

/* Роли */
.roles-container {
    min-width: 200px;
}

.role-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin: 0 8px 8px 0;
    transition: all 0.3s;
}

.role-admin {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.role-moderator {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.role-default {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
    color: white;
}

.role-remove {
    color: inherit;
    opacity: 0.7;
    text-decoration: none;
    font-size: 11px;
    transition: opacity 0.3s;
}

.role-remove:hover {
    opacity: 1;
}

.no-roles {
    color: #94a3b8;
    font-style: italic;
    font-size: 14px;
}

.role-form {
    margin-top: 10px;
}

.role-select-wrapper {
    display: flex;
    gap: 5px;
}

.role-select {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    background: white;
}

.role-add-btn {
    padding: 8px 12px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.role-add-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Дата */
.date-wrapper {
    min-width: 120px;
}

.date-day {
    color: #1e293b;
    font-weight: 500;
    font-size: 15px;
}

.date-time {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 4px;
}

/* Действия */
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
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.action-view {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.action-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-form {
    display: inline;
}

/* Пагинация */
.pagination-wrapper {
    padding: 25px;
    border-top: 1px solid #f1f5f9;
}

.pagination-wrapper nav {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    margin: 0;
}

/* Модальные окна */
.modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    border-bottom: 1px solid #f1f5f9;
    padding: 25px;
}

.modal-title {
    color: #1e293b;
    font-weight: 700;
    font-size: 20px;
    margin: 0;
}

.modal-body {
    padding: 30px;
}

.user-profile {
    max-width: 600px;
    margin: 0 auto;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #f1f5f9;
}

.profile-avatar-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.profile-avatar-default {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 36px;
    border: 5px solid white;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.profile-info {
    flex: 1;
}

.profile-name {
    color: #1e293b;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.profile-email {
    color: #64748b;
    font-size: 16px;
    margin: 0 0 12px 0;
}

.profile-id {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    font-family: monospace;
}

.profile-details {
    display: grid;
    gap: 20px;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s;
}

.detail-row:hover {
    background: #f1f5f9;
    transform: translateX(5px);
}

.detail-label {
    flex: 0 0 180px;
    color: #475569;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.detail-value {
    flex: 1;
    color: #1e293b;
    font-size: 16px;
}

.profile-role-tag {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin-right: 8px;
    margin-bottom: 8px;
}

.modal-footer {
    border-top: 1px solid #f1f5f9;
    padding: 20px 30px;
}

.btn-outline-secondary {
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 500;
}

.btn-primary {
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 500;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a3f8f 100%);
    transform: translateY(-2px);
}

/* Адаптивность */
@media (max-width: 1200px) {
    .admin-container {
        padding: 20px 15px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .admin-navigation {
        flex-wrap: wrap;
    }
    
    .nav-item {
        flex: 0 0 calc(50% - 2.5px);
        font-size: 14px;
        padding: 12px 15px;
    }
    
    .search-filters {
        flex-direction: column;
    }
    
    .filter-select {
        min-width: 100%;
    }
    
    .search-btn {
        min-width: 100%;
    }
    
    .export-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-table {
        display: block;
        overflow-x: auto;
    }
    
    .admin-table th,
    .admin-table td {
        white-space: nowrap;
        min-width: 150px;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .detail-row {
        flex-direction: column;
        gap: 8px;
    }
    
    .detail-label {
        flex: 0 0 auto;
    }
}

@media (max-width: 480px) {
    .admin-title {
        font-size: 24px;
    }
    
    .admin-subtitle {
        font-size: 14px;
    }
    
    .btn-back {
        padding: 8px 15px;
        font-size: 14px;
    }
    
    .nav-item {
        flex: 0 0 100%;
    }
}
</style>

<!-- Bootstrap JS для модальных окон -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Управление экспортом
function toggleExportOptions() {
    const exportOptions = document.getElementById('exportOptions');
    const toggleBtn = document.querySelector('.export-toggle-btn i');
    
    if (exportOptions.style.display === 'none' || !exportOptions.style.display) {
        exportOptions.style.display = 'block';
        toggleBtn.classList.remove('fa-chevron-down');
        toggleBtn.classList.add('fa-chevron-up');
    } else {
        exportOptions.style.display = 'none';
        toggleBtn.classList.remove('fa-chevron-up');
        toggleBtn.classList.add('fa-chevron-down');
    }
}

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Переключение форматов
    const formatOptions = document.querySelectorAll('.format-option');
    formatOptions.forEach(option => {
        option.addEventListener('click', function() {
            formatOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
        
        const radio = this.querySelector('input[type="radio"]');
        if (radio.checked) {
            this.classList.add('active');
        }
    });
    
    // Установка дат по умолчанию
    const today = new Date().toISOString().split('T')[0];
    const monthAgo = new Date();
    monthAgo.setMonth(monthAgo.getMonth() - 1);
    const monthAgoStr = monthAgo.toISOString().split('T')[0];
    
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    if (startDateInput && !startDateInput.value) {
        startDateInput.value = monthAgoStr;
    }
    
    if (endDateInput && !endDateInput.value) {
        endDateInput.value = today;
    }
    
    // Анимация для статистики
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Подтверждение удаления
    const deleteForms = document.querySelectorAll('.action-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection