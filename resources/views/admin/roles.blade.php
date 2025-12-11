@extends('sample.main')

@section('header-title')
Управление ролями
@endsection

@section('content')
<!-- Добавляем Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="roles-main-container">
    <!-- Заголовок и навигация -->
    <div class="roles-header">
        <h1>🛡️ Управление ролями</h1>
        <div class="roles-nav">
            <a href="{{ route('admin.dashboard') }}">📊 Дашборд</a>
            <a href="{{ route('admin.users') }}">👥 Пользователи</a>
            <a href="{{ route('admin.fields') }}">📍 Поля</a>
            <a href="{{ route('admin.roles') }}" class="nav-active">🛡️ Роли</a>
            <a href="/" class="nav-exit">← На сайт</a>
        </div>
    </div>
    
    <!-- Предупреждение -->
    <div class="roles-warning-card">
        <div class="warning-icon">⚠️</div>
        <div class="warning-content">
            <h4 class="warning-title">Системные роли защищены</h4>
            <p class="warning-text">
                Роли <strong>admin</strong> и <strong>user</strong> являются системными и не могут быть удалены или изменены. 
                Роль <strong>user</strong> назначается автоматически всем новым пользователям.
            </p>
        </div>
    </div>
    
    <!-- Кнопка создания новой роли -->
    <div class="create-role-section">
        <button class="btn-create-role" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="fas fa-plus-circle"></i> Создать новую роль
        </button>
    </div>
    
    <!-- Таблица ролей -->
    <div class="roles-table-container">
        <div class="table-header">
            <h2>Список ролей</h2>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Поиск по названию роли...">
            </div>
        </div>
        
        <table class="roles-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">Название роли</th>
                    <th class="col-users">Пользователей</th>
                    <th class="col-date">Дата создания</th>
                    <th class="col-status">Статус</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                @php
                    $userCount = DB::table('user_role_assignments')
                        ->where('role_id', $role->role_id)
                        ->count();
                @endphp
                <tr>
                    <td class="col-id">
                        <span class="role-id">#{{ $role->role_id }}</span>
                    </td>
                    <td class="col-name">
                        <div class="role-info">
                            <div class="role-icon">
                                @if($role->role_name == 'admin') 
                                    <i class="fas fa-crown"></i>
                                @elseif($role->role_name == 'user') 
                                    <i class="fas fa-user"></i>
                                @else 
                                    <i class="fas fa-user-tag"></i>
                                @endif
                            </div>
                            <div>
                                <div class="role-name">{{ $role->role_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="col-users">
                        @if($userCount > 0)
                        <span class="user-count">
                            <span class="user-count-badge">{{ $userCount }}</span>
                        </span>
                        @else
                        <span class="no-users">-</span>
                        @endif
                    </td>
                    <td class="col-date">
                        <span class="role-date">{{ date('d.m.Y', strtotime($role->created_at)) }}</span>
                    </td>
                    <td class="col-status">
                        @if(in_array($role->role_name, ['admin', 'user']))
                        <span class="status-badge status-system">
                            Системная
                        </span>
                        @else
                        <span class="status-badge status-editable">
                            Редактируемая
                        </span>
                        @endif
                    </td>
                    <td class="col-actions">
                        <div class="action-buttons">
                            @if(!in_array($role->role_name, ['admin', 'user']))
                            <!-- Кнопка редактирования -->
                            <button class="action-btn edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $role->role_id }}"
                                    title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Кнопка удаления -->
                            <form method="POST" 
                                  action="{{ route('admin.roles.delete', $role->role_id )}}"
                                  class="action-form"
                                  onsubmit="return confirm('Вы уверены, что хотите удалить эту роль?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete-btn" title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <!-- Кнопка заблокировано для системных ролей -->
                            <button class="action-btn lock-btn" title="Системная роль" disabled>
                                <i class="fas fa-lock"></i>
                            </button>
                            @endif
                            
                            <!-- Кнопка просмотра пользователей -->
                            @if($userCount > 0)
                            <button class="action-btn view-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#usersModal{{ $role->role_id }}"
                                    title="Просмотреть пользователей">
                                <i class="fas fa-eye"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Модальные окна -->
@foreach($roles as $role)
@php
    $usersWithRole = DB::table('users as u')
        ->join('user_role_assignments as ura', 'u.user_id', '=', 'ura.user_id')
        ->where('ura.role_id', $role->role_id)
        ->select('u.*')
        ->get();
@endphp

@if(!in_array($role->role_name, ['admin', 'user']) || $usersWithRole->count() > 0)
<div class="modal fade" id="usersModal{{ $role->role_id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Пользователи с ролью "{{ $role->role_name }}"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($usersWithRole->count() > 0)
                <div class="users-list-modal">
                    @foreach($usersWithRole as $user)
                    <div class="user-item-modal">
                        <div class="user-avatar-modal">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" 
                                     alt="Аватар {{ $user->username }}"
                                     class="avatar-image-modal">
                            @else
                                <div class="avatar-initials-modal">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="user-info-modal">
                            <strong class="user-name-modal">{{ $user->username }}</strong>
                            <small class="user-email-modal">{{ $user->email }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="no-users-found">
                    <i class="fas fa-user-slash no-users-icon"></i>
                    <p class="no-users-text">Пользователи не найдены</p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Закрыть
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if(!in_array($role->role_name, ['admin', 'user']))
<div class="modal fade" id="editModal{{ $role->role_id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Редактирование роли "{{ $role->role_name }}"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.roles.update', $role->role_id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-tag me-1"></i>Название роли</label>
                        <input type="text" name="role_name" class="form-control" value="{{ $role->role_name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Отмена
                    </button>
                    <button type="submit" class="btn-modal-save">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Создание новой роли</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-tag me-1"></i>Название роли</label>
                        <input type="text" name="role_name" class="form-control" placeholder="Введите название роли" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Отмена
                    </button>
                    <button type="submit" class="btn-modal-create">
                        <i class="fas fa-check"></i> Создать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Основные стили */
.roles-main-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.roles-header {
    margin-bottom: 40px;
}

.roles-header h1 {
    color: #47866A;
    margin: 0 0 20px 0;
    font-size: 32px;
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

/* Предупреждение */
.roles-warning-card {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 30px;
}

.warning-icon {
    font-size: 20px;
    margin-top: 3px;
}

.warning-content {
    flex: 1;
}

.warning-title {
    margin: 0 0 8px 0;
    color: #856404;
    font-size: 16px;
}

.warning-text {
    margin: 0;
    color: #856404;
    font-size: 14px;
    line-height: 1.5;
}

/* Кнопка создания новой роли */
.create-role-section {
    margin-bottom: 30px;
    text-align: right;
}

.btn-create-role {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-create-role:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-create-role i {
    font-size: 18px;
}

/* Таблица ролей */
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

.search-box {
    position: relative;
    min-width: 300px;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 14px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 35px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    background: #f8f9fa;
}

.search-box input:focus {
    outline: none;
    border-color: #47866A;
    background: white;
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

.role-id {
    color: #666;
    font-weight: 500;
    font-size: 13px;
}

.col-name {
    min-width: 150px;
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
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
}

.role-icon i.fa-crown {
    background: linear-gradient(90deg, #f59e0b, #d97706);
    padding: 10px;
    border-radius: 8px;
}

.role-icon i.fa-user {
    background: linear-gradient(90deg, #10b981, #059669);
    padding: 10px;
    border-radius: 8px;
}

.role-icon i.fa-user-tag {
    background: linear-gradient(90deg, #8b5cf6, #7c3aed);
    padding: 10px;
    border-radius: 8px;
}

.role-name {
    font-weight: 600;
    color: #333;
    font-size: 15px;
}

.col-users {
    width: 120px;
}

.user-count {
    background: #e7f2ff;
    color: #0066cc;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 500;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.user-count-badge {
    background: #0066cc;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
}

.no-users {
    color: #999;
    font-style: italic;
    font-size: 13px;
}

.col-date {
    width: 120px;
}

.role-date {
    color: #666;
    font-size: 13px;
    font-weight: 500;
}

.col-status {
    width: 140px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.status-system {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-editable {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Кнопки действий - ЯРКИЕ ИКОНКИ */
.col-actions {
    width: 180px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: nowrap;
}

.action-btn {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.edit-btn {
    background: linear-gradient(135deg, #47866A, #5CA08A);
}

.edit-btn:hover {
    background: linear-gradient(135deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(71, 134, 106, 0.4);
}

.delete-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.delete-btn:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
}

.lock-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    cursor: not-allowed;
}

.lock-btn:hover {
    transform: none;
    box-shadow: none;
}

.view-btn {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.view-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.action-form {
    display: inline-block;
    margin: 0;
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

.users-list-modal {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 5px;
}

.user-item-modal {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.2s;
}

.user-item-modal:hover {
    background: #e9ecef;
}

.user-avatar-modal {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(90deg, #47866A, #5CA08A);
}

.avatar-image-modal {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.avatar-initials-modal {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    font-weight: bold;
    border-radius: 50%;
}

.user-info-modal {
    flex-grow: 1;
    min-width: 0;
}

.user-name-modal {
    display: block;
    font-size: 14px;
    color: #333;
    font-weight: 500;
    margin-bottom: 2px;
}

.user-email-modal {
    display: block;
    color: #666;
    font-size: 13px;
}

.no-users-found {
    text-align: center;
    padding: 30px 0;
}

.no-users-icon {
    font-size: 40px;
    color: #999;
    margin-bottom: 10px;
}

.no-users-text {
    color: #666;
    margin: 0;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-group label i {
    color: #47866A;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    background: #f8f9fa;
}

.form-control:focus {
    outline: none;
    border-color: #47866A;
    background: white;
}

.modal-footer {
    border-top: 2px solid #f0f0f0;
    padding-top: 15px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-modal-cancel,
.btn-modal-save,
.btn-modal-create {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-modal-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.btn-modal-cancel:hover {
    background: #e9ecef;
    color: #333;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-modal-cancel i {
    font-size: 14px;
}

.btn-modal-save {
    background: linear-gradient(135deg, #47866A, #5CA08A);
    color: white;
}

.btn-modal-save:hover {
    background: linear-gradient(135deg, #3a7557, #4A8C74);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(71, 134, 106, 0.3);
}

.btn-modal-save i {
    font-size: 14px;
}

.btn-modal-create {
    background: linear-gradient(135deg, #47866A, #5CA08A);
    color: white;
}

.btn-modal-create:hover {
    background: linear-gradient(135deg, #3a7557, #4A8C74);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(71, 134, 106, 0.3);
}

.btn-modal-create i {
    font-size: 14px;
}

/* Адаптивность */
@media (max-width: 768px) {
    .roles-main-container {
        padding: 15px;
    }
    
    .roles-nav {
        flex-direction: column;
    }
    
    .nav-exit {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        min-width: 100%;
    }
    
    .roles-table {
        font-size: 12px;
    }
    
    .roles-table th,
    .roles-table td {
        padding: 10px;
    }
    
    .action-buttons {
        gap: 6px;
    }
    
    .action-btn {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }
    
    .create-role-section {
        text-align: center;
    }
    
    .btn-create-role {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .roles-warning-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 10px;
    }
    
    .user-item-modal {
        padding: 8px;
    }
    
    .user-avatar-modal {
        width: 36px;
        height: 36px;
    }
    
    .avatar-initials-modal {
        font-size: 16px;
    }
    
    .btn-modal-cancel,
    .btn-modal-save,
    .btn-modal-create {
        padding: 8px 15px;
        font-size: 13px;
    }
    
    .action-buttons {
        flex-wrap: wrap;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection