@extends('sample.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-warning text-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-user-shield me-2"></i>Управление ролями
                        </h4>
                        <p class="mb-0 mt-1 small opacity-75">Создание и управление ролями пользователей</p>
                    </div>
                    <button type="button" class="btn btn-light btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="fas fa-plus-circle me-2"></i> Новая роль
                    </button>
                </div>
                
                <div class="card-body">
                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card border-start border-warning border-4 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-uppercase text-muted mb-1">Всего ролей</h6>
                                            <h3 class="mb-0">{{ $roles->count() }}</h3>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                                            <i class="fas fa-layer-group text-warning fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-start border-primary border-4 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-uppercase text-muted mb-1">Системные</h6>
                                            <h3 class="mb-0">{{ $roles->whereIn('role_name', ['admin', 'user'])->count() }}</h3>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                                            <i class="fas fa-shield-alt text-primary fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-start border-success border-4 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-uppercase text-muted mb-1">Пользовательские</h6>
                                            <h3 class="mb-0">{{ $roles->whereNotIn('role_name', ['admin', 'user'])->count() }}</h3>
                                        </div>
                                        <div class="bg-success bg-opacity-10 p-3 rounded">
                                            <i class="fas fa-user-tag text-success fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-start border-info border-4 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-uppercase text-muted mb-1">Всего пользователей</h6>
                                            <h3 class="mb-0">{{ DB::table('users')->count() }}</h3>
                                        </div>
                                        <div class="bg-info bg-opacity-10 p-3 rounded">
                                            <i class="fas fa-users text-info fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Навигация -->
                    <div class="admin-nav mb-4">
                        <div class="d-flex flex-wrap gap-2 border-bottom pb-3">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="fas fa-tachometer-alt me-1"></i> Дашборд
                            </a>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="fas fa-users me-1"></i> Пользователи
                            </a>
                            <a href="{{ route('admin.fields') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="fas fa-map-marked-alt me-1"></i> Поля
                            </a>
                            <a href="{{ route('admin.roles') }}" class="btn btn-warning rounded-pill">
                                <i class="fas fa-user-shield me-1"></i> Роли
                            </a>
                            <a href="/" class="btn btn-outline-secondary rounded-pill ms-auto">
                                <i class="fas fa-arrow-left me-1"></i> На сайт
                            </a>
                        </div>
                    </div>
                    
                    <!-- Предупреждение о системных ролях -->
                    <div class="alert alert-warning border-warning bg-warning bg-opacity-10 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Системные роли защищены</h6>
                                <p class="mb-0">
                                    Роли <span class="badge bg-primary">admin</span> и <span class="badge bg-secondary">user</span> 
                                    являются системными и не могут быть удалены или изменены. 
                                    Роль <strong>user</strong> назначается автоматически всем новым пользователям.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Таблица ролей -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Список всех ролей</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">ID</th>
                                            <th>Название роли</th>
                                            <th class="text-center">Пользователей</th>
                                            <th>Дата создания</th>
                                            <th class="text-center">Статус</th>
                                            <th class="text-end pe-4">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                        @php
                                            $userCount = DB::table('user_role_assignments')
                                                ->where('role_id', $role->role_id)
                                                ->count();
                                        @endphp
                                        <tr class="{{ in_array($role->role_name, ['admin', 'user']) ? 'table-active' : '' }}">
                                            <td class="ps-4">
                                                <span class="badge bg-dark">#{{ $role->role_id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle me-3">
                                                        <i class="fas fa-user-shield"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $role->role_name }}</strong>
                                                        @if(in_array($role->role_name, ['admin', 'user']))
                                                            <span class="badge bg-primary mt-1">Системная</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($userCount > 0)
                                                <a href="javascript:void(0)" 
                                                   class="text-decoration-none"
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#roleUsersModal{{ $role->role_id }}">
                                                    <span class="badge bg-info rounded-pill px-3 py-2">
                                                        {{ $userCount }} <i class="fas fa-eye ms-1"></i>
                                                    </span>
                                                </a>
                                                @else
                                                <span class="badge bg-light text-muted rounded-pill px-3 py-2">
                                                    <i class="fas fa-user-slash me-1"></i>Нет
                                                </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    {{ date('d.m.Y', strtotime($role->created_at)) }}
                                                    <br>
                                                    <small>{{ date('H:i', strtotime($role->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if(in_array($role->role_name, ['admin', 'user']))
                                                <span class="badge bg-primary py-2 px-3">
                                                    <i class="fas fa-shield-alt me-1"></i>Системная
                                                </span>
                                                @else
                                                <span class="badge bg-success py-2 px-3">
                                                    <i class="fas fa-edit me-1"></i>Редактируемая
                                                </span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if(!in_array($role->role_name, ['admin', 'user']))
                                                        <button type="button" 
                                                                class="btn btn-outline-warning border-end-0 rounded-start"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editRoleModal{{ $role->role_id }}"
                                                                title="Редактировать">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" 
                                                              action="#"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Удалить эту роль? Пользователи потеряют права.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger rounded-end">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">
                                                            <i class="fas fa-lock me-1"></i>Системная
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted">
                                        Показано {{ $roles->count() }} из {{ $roles->count() }} ролей
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>Обновлено: {{ now()->format('d.m.Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно создания роли -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Создание новой роли
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.roles.create') }}" id="createRoleForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Название роли *</label>
                        <input type="text" 
                               name="role_name" 
                               class="form-control form-control-lg border-warning border-2"
                               placeholder="moderator, manager, editor"
                               required
                               autofocus>
                        <div class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Используйте только латинские буквы, цифры и подчеркивания
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <i class="fas fa-lightbulb text-info me-3 mt-1"></i>
                            <div>
                                <small class="d-block mb-1"><strong>Совет:</strong></small>
                                <small class="d-block mb-0">После создания роли вы сможете назначить её пользователям в разделе "Пользователи".</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-2"></i>Создать роль
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальные окна для каждой роли -->
@foreach($roles as $role)
@php
    $usersWithRole = DB::table('users as u')
        ->join('user_role_assignments as ura', 'u.user_id', '=', 'ura.user_id')
        ->where('ura.role_id', $role->role_id)
        ->select('u.*')
        ->get();
@endphp

<!-- Модальное окно пользователей роли -->
<div class="modal fade" id="roleUsersModal{{ $role->role_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>Пользователи с ролью "{{ $role->role_name }}"
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                @if($usersWithRole->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Дата назначения</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usersWithRole as $user)
                            <tr>
                                <td class="ps-4">#{{ $user->user_id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                            {{ strtoupper(substr($user->username, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->username }}</h6>
                                            <small class="text-muted">ID: {{ $user->user_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $assignment = DB::table('user_role_assignments')
                                            ->where('user_id', $user->user_id)
                                            ->where('role_id', $role->role_id)
                                            ->first();
                                    @endphp
                                    @if($assignment && $assignment->created_at)
                                    <small class="text-muted">
                                        {{ date('d.m.Y', strtotime($assignment->created_at)) }}
                                        <br>
                                        {{ date('H:i', strtotime($assignment->created_at)) }}
                                    </small>
                                    @else
                                    <small class="text-muted">Не указано</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-slash fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">Пользователи не найдены</h5>
                    <p class="text-muted mb-0">Этой роли еще не назначен ни один пользователь</p>
                </div>
                @endif
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <a href="{{ route('admin.users') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Управление пользователями
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования роли -->
<div class="modal fade" id="editRoleModal{{ $role->role_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Редактирование роли "{{ $role->role_name }}"
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Название роли *</label>
                        <input type="text" 
                               name="role_name" 
                               class="form-control form-control-lg border-warning border-2"
                               value="{{ $role->role_name }}"
                               required>
                    </div>
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle text-warning me-3 mt-1"></i>
                            <div>
                                <small class="d-block mb-1"><strong>Внимание:</strong></small>
                                <small class="d-block mb-0">Изменение названия роли может повлиять на логику работы системы.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-2"></i>Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<style>
.avatar {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
.table-hover tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.05);
}
.border-warning {
    border-color: #ffc107 !important;
}
.bg-warning {
    background-color: #ffc107 !important;
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107, #ff9800) !important;
}
.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}
.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #000;
}
.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
}
</style>
@endsection