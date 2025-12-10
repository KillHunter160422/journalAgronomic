@extends('sample.main')

@section('header-title')
Мой профиль
@endsection

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="Аватар" class="avatar-img">
            @else
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($user->username, 0, 2)) }}
                </div>
            @endif
        </div>
        <div class="profile-info">
            <h1>{{ $user->username }}</h1>
            @if($user->fullname)
                <p class="profile-fullname">{{ $user->fullname }}</p>
            @endif
            <p class="profile-email">{{ $user->email }}</p>
            <p class="profile-joined">Зарегистрирован: {{ date('d.m.Y', strtotime($user->created_at)) }}</p>
        </div>
        <div class="profile-actions">
            <a href="{{ route('profile.edit') }}" class="btn-edit-profile">✏️ Редактировать</a>
            @if(php_auth_check() && strpos($user->email, 'admin') !== false)
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">⚙️ Админ-панель</a>
            @endif
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📍</div>
            <div class="stat-content">
                <h3>{{ $stats['fields_count'] }}</h3>
                <p>Поля</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🔧</div>
            <div class="stat-content">
                <h3>{{ $stats['operations_count'] }}</h3>
                <p>Операции</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>{{ $stats['surveys_count'] }}</h3>
                <p>Обследования</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🌐</div>
            <div class="stat-content">
                <h3>{{ $stats['public_fields'] }}</h3>
                <p>Публичные поля</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📏</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_area'], 1) }}</h3>
                <p>Гектаров всего</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-content">
                <h3>#{{ $user->user_id }}</h3>
                <p>ID пользователя</p>
            </div>
        </div>
    </div>
    
    <!-- Мои поля -->
    <div class="section-card">
        <div class="section-header">
            <h2>Мои поля</h2>
            <a href="{{ route('fields.create') }}" class="btn-add">➕ Добавить поле</a>
        </div>
        
        @php
            $userFields = DB::table('fields')
                ->where('user_id', php_session('user_id'))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        @endphp
        
        @if($userFields->count() > 0)
        <div class="fields-list">
            @foreach($userFields as $field)
            <div class="field-item">
                <div class="field-main">
                    <h4>{{ $field->field_name }}</h4>
                    <div class="field-meta">
                        <span>{{ $field->field_area }} га</span>
                        <span class="badge {{ $field->is_public ? 'public' : 'private' }}">
                            {{ $field->is_public ? 'Публичное' : 'Приватное' }}
                        </span>
                        <span>{{ date('d.m.Y', strtotime($field->created_at)) }}</span>
                    </div>
                </div>
                <div class="field-actions">
                    <a href="/fields/{{ $field->field_id }}" class="btn-view">👁️ Просмотр</a>
                    <a href="{{ route('operations.create', $field->field_id) }}" class="btn-add-op">➕ Операция</a>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($stats['fields_count'] > 5)
        <div class="view-all">
            <a href="/fields?user={{ $user->user_id }}">Посмотреть все поля →</a>
        </div>
        @endif
        
        @else
        <p class="empty-state">У вас пока нет полей. <a href="{{ route('fields.create') }}">Создайте первое поле</a></p>
        @endif
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.profile-header {
    display: flex;
    gap: 30px;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.profile-avatar {
    flex-shrink: 0;
}

.avatar-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #47866A;
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #47866A, #5CA08A);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: bold;
    border: 4px solid #e9ecef;
}

.profile-info {
    flex-grow: 1;
}

.profile-info h1 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 32px;
}

.profile-fullname {
    font-size: 18px;
    color: #666;
    margin: 0 0 8px 0;
}

.profile-email {
    font-size: 16px;
    color: #47866A;
    margin: 0 0 8px 0;
}

.profile-joined {
    font-size: 14px;
    color: #999;
    margin: 0;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-edit-profile, .btn-admin {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s;
}

.btn-edit-profile {
    background: #47866A;
    color: white;
    border: none;
}

.btn-edit-profile:hover {
    background: #3a7557;
    transform: translateY(-2px);
}

.btn-admin {
    background: #6c757d;
    color: white;
    border: none;
}

.btn-admin:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Статистика */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 32px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 28px;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

/* Секции */
.section-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
    color: #333;
    font-size: 22px;
}

.btn-add {
    background: #47866A;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-add:hover {
    background: #3a7557;
    transform: translateY(-2px);
}

/* Список полей */
.fields-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.field-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #47866A;
}

.field-main h4 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 16px;
}

.field-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.badge.public {
    background: #e7f7ef;
    color: #2e7d5f;
}

.badge.private {
    background: #f5f5f5;
    color: #666;
}

.field-actions {
    display: flex;
    gap: 10px;
}

.btn-view, .btn-add-op {
    padding: 5px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.3s;
}

.btn-view {
    background: #e3f2fd;
    color: #1565c0;
}

.btn-add-op {
    background: #f8f9fa;
    color: #47866A;
    border: 1px solid #ddd;
}

.view-all {
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.view-all a {
    color: #47866A;
    text-decoration: none;
    font-weight: 500;
}

.view-all a:hover {
    text-decoration: underline;
}

.empty-state {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px;
}

.empty-state a {
    color: #47866A;
    text-decoration: none;
}

.empty-state a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-actions {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .field-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .field-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
@endsection