@extends('sample.main')

@section('content')
<div class="admin-container">
    <!-- Заголовок и навигация -->
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="admin-title">
                    <i class="fas fa-map-marked-alt me-2"></i>Управление полями
                </h1>
                <p class="admin-subtitle">Всего полей: <span class="badge-count">{{ $fields->total() }}</span></p>
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
            <a href="{{ route('admin.users') }}" class="nav-item">
                <i class="fas fa-users me-2"></i> Пользователи
            </a>
            <a href="{{ route('admin.fields') }}" class="nav-item active">
                <i class="fas fa-map-marked-alt me-2"></i> Поля
            </a>
            <a href="{{ route('admin.roles') }}" class="nav-item">
                <i class="fas fa-user-shield me-2"></i> Роли
            </a>
        </div>
    </div>
    
    <!-- Поиск и фильтры -->
    <div class="search-section">
        <form method="GET" action="{{ route('admin.fields') }}">
            <div class="search-wrapper">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Поиск по названию поля..."
                           value="{{ request('search') }}">
                    @if(request('search'))
                    <a href="{{ route('admin.fields') }}" class="search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
                
                <div class="search-filters">
                    <select name="visibility" class="filter-select">
                        <option value="">Все поля</option>
                        <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>
                            Публичные
                        </option>
                        <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>
                            Приватные
                        </option>
                    </select>
                    
                    <select name="owner" class="filter-select">
                        <option value="">Все владельцы</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}" {{ request('owner') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->username }}
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search me-1"></i> Найти
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Статистика полей -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-map"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['total_fields'] ?? 0 }}</h3>
                <p>Всего полей</p>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['public_fields'] ?? 0 }}</h3>
                <p>Публичных</p>
            </div>
        </div>
        
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['private_fields'] ?? 0 }}</h3>
                <p>Приватных</p>
            </div>
        </div>
        
        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-ruler-combined"></i>
            </div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_area'] ?? 0, 1) }}</h3>
                <p>Гектаров всего</p>
            </div>
        </div>
    </div>
    
    <!-- Таблица полей -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-name">Название поля</th>
                        <th class="col-owner">Владелец</th>
                        <th class="col-area">Площадь</th>
                        <th class="col-status">Статус</th>
                        <th class="col-date">Дата создания</th>
                        <th class="col-actions">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fields as $field)
                    <tr>
                        <td class="col-id">
                            <span class="field-id">{{ $field->field_id }}</span>
                        </td>
                        <td class="col-name">
                            <div class="field-info">
                                <div class="field-name">{{ $field->field_name }}</div>
                                @if($field->polygon)
                                <div class="field-coordinates">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Координаты: {{ Str::limit($field->polygon, 30) }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="col-owner">
                            <div class="owner-info">
                                <div class="owner-avatar">
                                    @if($field->avatar_url)
                                        <img src="{{ $field->avatar_url }}" 
                                             alt="{{ $field->username }}" 
                                             class="avatar-img">
                                    @else
                                        <div class="avatar-default">
                                            {{ strtoupper(substr($field->username, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="owner-details">
                                    <div class="owner-name">{{ $field->username }}</div>
                                    <div class="owner-email">{{ $field->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="col-area">
                            <div class="area-badge">
                                <i class="fas fa-ruler-combined"></i>
                                <span>{{ number_format($field->field_area, 2) }} га</span>
                            </div>
                        </td>
                        <td class="col-status">
                            <div class="status-badge {{ $field->is_public == 1 ? 'status-public' : 'status-private' }}">
                                <i class="fas {{ $field->is_public == 1 ? 'fa-globe' : 'fa-lock' }}"></i>
                                <span>{{ $field->is_public == 1 ? 'Публичное' : 'Приватное' }}</span>
                            </div>
                        </td>
                        <td class="col-date">
                            <div class="date-wrapper">
                                <div class="date-day">{{ date('d.m.Y', strtotime($field->created_at)) }}</div>
                                <div class="date-time">{{ date('H:i', strtotime($field->created_at)) }}</div>
                            </div>
                        </td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <a href="/fields/{{ $field->field_id }}" 
                                   class="action-btn action-view"
                                   target="_blank"
                                   title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="/fields/{{ $field->field_id }}/edit" 
                                   class="action-btn action-edit"
                                   title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form method="POST" 
                                      action="{{ route('admin.fields.delete', $field->field_id) }}"
                                      class="action-form"
                                      onsubmit="return confirm('Удалить поле \"{{ $field->field_name }}\"?')">
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
        
        @if($fields->hasPages())
        <div class="pagination-wrapper">
            {{ $fields->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

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
    background: #10b981;
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
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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
    border-color: #10b981;
    background: white;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
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
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

.search-btn {
    padding: 12px 30px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.stat-success {
    border-left-color: #10b981;
}

.stat-info {
    border-left-color: #3b82f6;
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

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

.field-id {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    font-family: monospace;
}

.col-name {
    min-width: 250px;
}

.field-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.field-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 16px;
}

.field-coordinates {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 13px;
}

.field-coordinates i {
    color: #94a3b8;
    font-size: 12px;
}

.col-owner {
    min-width: 200px;
}

.owner-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.owner-avatar {
    flex-shrink: 0;
}

.avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.avatar-default {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.owner-details {
    flex: 1;
    min-width: 0;
}

.owner-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 2px;
}

.owner-email {
    color: #64748b;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.col-area {
    width: 120px;
}

.area-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    color: #0369a1;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}

.area-badge i {
    font-size: 14px;
}

.col-status {
    width: 120px;
}

.status-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-public {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
}

.status-private {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #475569;
    border: 1px solid #cbd5e1;
}

.status-badge i {
    font-size: 11px;
}

.col-date {
    width: 130px;
}

.date-wrapper {
    min-width: 120px;
}

.date-day {
    color: #1e293b;
    font-weight: 500;
    font-size: 14px;
}

.date-time {
    color: #94a3b8;
    font-size: 12px;
    margin-top: 4px;
}

/* Действия */
.col-actions {
    width: 120px;
}

.action-buttons {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s;
    text-decoration: none;
}

.action-view {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.action-edit {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
    
    .action-buttons {
        flex-direction: column;
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

<script>
// Инициализация
document.addEventListener('DOMContentLoaded', function() {
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
            const fieldName = this.closest('tr').querySelector('.field-name').textContent;
            if (!confirm(`Удалить поле "${fieldName}"?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection