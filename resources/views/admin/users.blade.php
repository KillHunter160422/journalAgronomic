@extends('sample.main')

@section('header-title')
Управление пользователями
@endsection

@section('content')
<!-- Добавляем Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-container">
    <!-- Заголовок и навигация ТОЧНО КАК В РОЛЯХ -->
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
                
                <!-- Кнопки экспорта и импорта -->
                <button type="button" class="export-btn" onclick="toggleExportPanel()">
                    <i class="fas fa-file-export me-1"></i> Экспорт
                </button>
                
                <button type="button" class="import-btn" onclick="toggleImportPanel()">
                    <i class="fas fa-file-import me-1"></i> Импорт
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
                        <i class="fas fa-columns me-2"></i>Выберите колонки
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
                            <input type="checkbox" name="filters[]" value="with_roles">
                            <span>Только пользователи с ролями</span>
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
    
    <!-- Панель импорта -->
    <div class="import-panel" id="importPanel" style="display: none;">
        <div class="import-header">
            <h4><i class="fas fa-file-import me-2"></i>Импорт данных пользователей</h4>
            <button type="button" class="close-panel-btn" onclick="toggleImportPanel()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="import-form">
            @csrf
            <div class="import-options">
                <div class="import-section">
                    <label class="import-label">
                        <i class="fas fa-file-upload me-2"></i>Выберите файл для импорта
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text">Перетащите файл сюда или нажмите для выбора</p>
                        <input type="file" name="import_file" id="importFile" accept=".csv,.xlsx,.xls" class="file-input" required>
                        <p class="file-types">Поддерживаемые форматы: CSV, Excel</p>
                    </div>
                    <div class="file-info" id="fileInfo" style="display: none;">
                        <div class="file-details">
                            <i class="fas fa-file"></i>
                            <div>
                                <span class="file-name" id="fileName"></span>
                                <span class="file-size" id="fileSize"></span>
                            </div>
                            <button type="button" class="remove-file-btn" onclick="removeFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="import-section">
                    <label class="import-label">
                        <i class="fas fa-cog me-2"></i>Настройки импорта
                    </label>
                    <div class="import-settings">
                        <div class="setting-option">
                            <label>
                                <input type="checkbox" name="skip_duplicates" checked>
                                <span>Пропускать дубликаты (по email)</span>
                            </label>
                        </div>
                        <div class="setting-option">
                            <label>
                                <input type="checkbox" name="send_welcome_email">
                                <span>Отправить приветственное письмо новым пользователям</span>
                            </label>
                        </div>
                        <div class="setting-option">
                            <label>
                                <input type="checkbox" name="assign_default_role" checked>
                                <span>Назначать роль "user" новым пользователям</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="import-section">
                    <label class="import-label">
                        <i class="fas fa-info-circle me-2"></i>Требования к файлу
                    </label>
                    <div class="requirements">
                        <div class="requirement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Файл должен содержать колонки: email, username</span>
                        </div>
                        <div class="requirement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Опциональные колонки: fullname</span>
                        </div>
                        <div class="requirement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Максимальный размер файла: 10MB</span>
                        </div>
                        <div class="requirement-item">
                            <i class="fas fa-download"></i>
                            <a href="{{ route('admin.users.import.template') }}" class="template-link">
                                <i class="fas fa-file-download me-1"></i> Скачать шаблон CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="import-actions">
                <button type="button" class="cancel-btn" onclick="toggleImportPanel()">
                    <i class="fas fa-times me-1"></i> Отмена
                </button>
                <button type="submit" class="confirm-import-btn">
                    <i class="fas fa-upload me-1"></i> Импортировать
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
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewUserModal{{ $user->user_id }}"
                                    title="Просмотр">
                                <i class="fas fa-eye"></i>
                            </button>
                            
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
                            @if(isset($user->is_active))
                                <div class="profile-status {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $user->is_active ? 'Активен' : 'Неактивен' }}
                                </div>
                            @endif
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
                                    <span class="profile-role-tag {{ $role == 'admin' ? 'role-admin' : ($role == 'moderator' ? 'role-moderator' : 'role-user') }}">
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
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
/* Основные стили - ТОЧНО КАК В РОЛЯХ */
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Заголовок и навигация - ТОЧНО КАК В РОЛЯХ */
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
.export-panel, .import-panel {
    background: white;
    border-radius: 12px;
    padding: 0;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.export-header, .import-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(90deg, #f8fafc 0%, #e2e8f0 100%);
    border-bottom: 1px solid #e2e8f0;
}

.export-header h4, .import-header h4 {
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

.export-form, .import-form {
    padding: 25px;
}

.export-options, .import-options {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.export-section, .import-section {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.export-label, .import-label {
    display: flex;
    align-items: center;
    color: #475569;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 15px;
}

.export-label i, .import-label i {
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

.export-actions, .import-actions {
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

/* Стили для импорта */
.file-upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}

.file-upload-area:hover {
    border-color: #47866A;
    background: #f8fafc;
}

.upload-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 15px;
}

.upload-text {
    color: #64748b;
    font-size: 16px;
    margin-bottom: 10px;
}

.file-input {
    display: none;
}

.file-types {
    color: #94a3b8;
    font-size: 14px;
    margin-top: 10px;
}

.file-info {
    margin-top: 15px;
}

.file-details {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.file-details i {
    font-size: 24px;
    color: #47866A;
}

.file-name {
    display: block;
    font-weight: 500;
    color: #475569;
}

.file-size {
    display: block;
    color: #94a3b8;
    font-size: 13px;
}

.remove-file-btn {
    margin-left: auto;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 5px;
}

.remove-file-btn:hover {
    color: #ef4444;
}

.import-settings {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.setting-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.setting-option label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    width: 100%;
}

.setting-option span {
    color: #475569;
    font-size: 14px;
}

.requirements {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #475569;
    font-size: 14px;
}

.requirement-item i {
    color: #10b981;
}

.template-link {
    color: #47866A;
    text-decoration: none;
    font-weight: 500;
}

.template-link:hover {
    text-decoration: underline;
    color: #3a7557;
}

.confirm-import-btn {
    padding: 12px 30px;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.confirm-import-btn:hover {
    background: linear-gradient(90deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
}

/* Кнопки экспорта/импорта в фильтрах */
.export-btn, .import-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.export-btn {
    background: linear-gradient(90deg, #f59e0b, #d97706);
    color: white;
}

.export-btn:hover {
    background: linear-gradient(90deg, #d97706, #b45309);
    transform: translateY(-2px);
}

.import-btn {
    background: linear-gradient(90deg, #8b5cf6, #7c3aed);
    color: white;
}

.import-btn:hover {
    background: linear-gradient(90deg, #7c3aed, #6d28d9);
    transform: translateY(-2px);
}

/* Таблица - ТОЧНО КАК В РОЛЯХ */
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

/* Кнопки действий - ТОЧНО КАК В ОПЕРАЦИЯХ */
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

/* Модальные окна */
.modal-header {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.modal-title {
    color: #333;
    font-size: 18px;
    margin: 0;
    display: flex;
    align-items: center;
}

.modal-title i {
    color: #47866A;
    margin-right: 10px;
}

.modal-body {
    padding: 20px 0;
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
    border-bottom: 1px solid #f0f0f0;
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
    background: linear-gradient(90deg, #47866A, #5CA08A);
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
    color: white;
}

.profile-role-tag.role-admin {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.profile-role-tag.role-moderator {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.profile-role-tag.role-user {
    background: linear-gradient(90deg, #10b981, #059669);
}

/* Кнопки в модальных окнах */
.modal-footer {
    border-top: 2px solid #f0f0f0;
    padding-top: 15px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.btn-modal-cancel {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.btn-modal-cancel:hover {
    background: #e9ecef;
    color: #333;
}

/* Добавляем стили для кнопок активации/деактивации */
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

/* Стили для статуса в модальном окне */
.profile-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 5px;
}

.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: #065f46;
    border: 1px solid #10b981;
}

.status-inactive {
    background: rgba(245, 158, 11, 0.1);
    color: #854d0e;
    border: 1px solid #f59e0b;
}

/* Дополнительные стили для форм */
.action-form {
    display: inline;
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
    .export-btn,
    .import-btn {
        width: 100%;
    }
    
    .export-panel, .import-panel {
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
    
    .file-upload-area {
        padding: 20px;
    }
    
    .upload-icon {
        font-size: 36px;
        margin-bottom: 10px;
    }
    
    .upload-text {
        font-size: 14px;
    }
    
    .export-actions, .import-actions {
        flex-direction: column;
    }
    
    .cancel-btn, .confirm-export-btn, .confirm-import-btn {
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
    
    .role-select {
        font-size: 12px;
        padding: 5px 8px;
    }
    
    .role-add-btn {
        padding: 5px 8px;
        font-size: 12px;
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
    
    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .profile-avatar-img,
    .profile-avatar-default {
        width: 80px;
        height: 80px;
        font-size: 28px;
    }
    
    .profile-name {
        font-size: 24px;
    }
    
    .detail-row {
        flex-direction: column;
        gap: 8px;
    }
    
    .detail-label {
        flex: 0 0 auto;
        font-size: 14px;
    }
    
    .detail-value {
        font-size: 14px;
    }
    
    .alert {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    .pagination-wrapper {
        padding: 15px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .table-header h2 {
        font-size: 18px;
    }
    
    .btn-refresh {
        width: 32px;
        height: 32px;
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
    
    .export-header, .import-header {
        padding: 15px;
    }
    
    .export-header h4, .import-header h4 {
        font-size: 16px;
    }
    
    .export-form, .import-form {
        padding: 15px;
    }
    
    .export-section, .import-section {
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
    
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-title {
        font-size: 16px;
    }
    
    .profile-name {
        font-size: 20px;
    }
    
    .profile-email {
        font-size: 14px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Функции для управления панелями экспорта/импорта
function toggleExportPanel() {
    const exportPanel = document.getElementById('exportPanel');
    const importPanel = document.getElementById('importPanel');
    
    if (exportPanel.style.display === 'none' || exportPanel.style.display === '') {
        exportPanel.style.display = 'block';
        if (importPanel.style.display === 'block') {
            importPanel.style.display = 'none';
        }
    } else {
        exportPanel.style.display = 'none';
    }
}

function toggleImportPanel() {
    const exportPanel = document.getElementById('exportPanel');
    const importPanel = document.getElementById('importPanel');
    
    if (importPanel.style.display === 'none' || importPanel.style.display === '') {
        importPanel.style.display = 'block';
        if (exportPanel.style.display === 'block') {
            exportPanel.style.display = 'none';
        }
    } else {
        importPanel.style.display = 'none';
    }
}

// Управление загрузкой файлов
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('importFile');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    if (fileInput && fileUploadArea) {
        // Клик по области загрузки
        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Перетаскивание файла
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#47866A';
            this.style.backgroundColor = '#f0f9ff';
        });
        
        fileUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#cbd5e1';
            this.style.backgroundColor = 'white';
        });
        
        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#cbd5e1';
            this.style.backgroundColor = 'white';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileInfo();
            }
        });
        
        // Изменение файла через input
        fileInput.addEventListener('change', updateFileInfo);
    }
    
    function updateFileInfo() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
            fileUploadArea.style.display = 'none';
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
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
});

// Остальные функции
document.addEventListener('DOMContentLoaded', function() {
    // Поиск и фильтры
    const searchInput = document.querySelector('.search-input');
    const filterSelects = document.querySelectorAll('.filter-select');
    
    // Авто-поиск при изменении фильтров
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
    
    // Подтверждение удаления
    const deleteForms = document.querySelectorAll('.action-form');
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                    e.preventDefault();
                }
            });
        }
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
});

function removeFile() {
    const fileInput = document.getElementById('importFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileUploadArea = document.getElementById('fileUploadArea');
    
    fileInput.value = '';
    fileInfo.style.display = 'none';
    fileUploadArea.style.display = 'block';
}
</script>
@endsection