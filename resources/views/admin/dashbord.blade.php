@extends('sample.main')

@section('header-title')
Админ-панель
@endsection

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1>⚙️ Админ-панель</h1>
        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-active">📊 Дашборд</a>
            <a href="{{ route('admin.users') }}">👥 Пользователи</a>
            <a href="{{ route('admin.fields') }}">📍 Поля</a>
            <a href="{{ route('admin.roles') }}">🛡️ Роли</a>
            <a href="/" class="nav-exit">← На сайт</a>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="admin-stats">
        @foreach([
            ['icon' => '👥', 'title' => 'Пользователей', 'value' => $stats['total_users']],
            ['icon' => '📍', 'title' => 'Полей', 'value' => $stats['total_fields']],
            ['icon' => '🔧', 'title' => 'Операций', 'value' => $stats['total_operations']],
            ['icon' => '📊', 'title' => 'Обследований', 'value' => $stats['total_surveys']],
            ['icon' => '🌐', 'title' => 'Публичных полей', 'value' => $stats['public_fields']],
            ['icon' => '🔒', 'title' => 'Приватных полей', 'value' => $stats['private_fields']],
            ['icon' => '📏', 'title' => 'Гектаров всего', 'value' => number_format($stats['total_area'], 1)],
            ['icon' => '🛡️', 'title' => 'Администраторов', 'value' => $stats['admin_users']],
        ] as $stat)
        <div class="stat-card admin">
            <div class="stat-icon">{{ $stat['icon'] }}</div>
            <div class="stat-content">
                <h3>{{ $stat['value'] }}</h3>
                <p>{{ $stat['title'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Быстрые действия -->
    <div class="quick-actions">
        <h2>Быстрые действия</h2>
        <div class="actions-grid">
            <a href="{{ route('admin.users') }}" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-text">Управление пользователями</div>
            </a>
            
            <a href="{{ route('admin.fields') }}" class="action-card">
                <div class="action-icon">📍</div>
                <div class="action-text">Все поля системы</div>
            </a>
            
            <a href="{{ route('admin.roles') }}" class="action-card">
                <div class="action-icon">🛡️</div>
                <div class="action-text">Управление ролями</div>
            </a>
            
            <a href="/fields/create" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-text">Создать новое поле</div>
            </a>
        </div>
    </div>
    
    <!-- Последние пользователи -->
    <div class="recent-section">
        <h2>Последние пользователи</h2>
        @php
            use Illuminate\Support\Facades\DB;
            $recentUsers = DB::table('users')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        @endphp
        
        <div class="recent-list">
            @foreach($recentUsers as $user)
            <div class="recent-item">
                <div class="recent-avatar">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="Аватар">
                    @else
                        <div class="avatar-small">
                            {{ strtoupper(substr($user->username, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="recent-info">
                    <h4>{{ $user->username }}</h4>
                    <p>{{ $user->email }}</p>
                    <small>{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</small>
                </div>
                <div class="recent-actions">
                    <a href="{{ route('admin.users') }}" class="btn-small">Просмотр</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* Стили админ-панели */
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.admin-header {
    margin-bottom: 40px;
}

.admin-header h1 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 32px;
}

.admin-nav {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.admin-nav a {
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-active {
    background: #47866A;
    color: white;
}

.admin-nav a:not(.nav-active) {
    background: #f8f9fa;
    color: #666;
}

.admin-nav a:not(.nav-active):hover {
    background: #e9ecef;
    color: #333;
}

.nav-exit {
    margin-left: auto;
    background: #6c757d !important;
    color: white !important;
}

.nav-exit:hover {
    background: #5a6268 !important;
}

.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card.admin {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-top: 4px solid #47866A;
}

.quick-actions {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.quick-actions h2 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 22px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.action-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    border: 1px solid #eee;
}

.action-card:hover {
    background: white;
    border-color: #47866A;
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.action-icon {
    font-size: 32px;
    margin-bottom: 15px;
}

.action-text {
    font-weight: 500;
    font-size: 16px;
}

.recent-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.recent-section h2 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 22px;
}

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.recent-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #47866A, #5CA08A);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
}

.recent-info {
    flex-grow: 1;
}

.recent-info h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 16px;
}

.recent-info p {
    margin: 0 0 5px 0;
    color: #666;
    font-size: 14px;
}

.recent-info small {
    color: #999;
    font-size: 12px;
}

.recent-actions {
    flex-shrink: 0;
}

.btn-small {
    padding: 6px 12px;
    background: #47866A;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
}

@media (max-width: 768px) {
    .admin-nav {
        flex-direction: column;
    }
    
    .nav-exit {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .admin-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .recent-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endsection