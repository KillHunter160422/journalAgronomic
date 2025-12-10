<!DOCTYPE html>
<html>
<head>
    <title>Отладка сессии</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .debug-section { 
            background: white; 
            margin-bottom: 20px; 
            padding: 20px; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .debug-title { 
            color: #2c5530; 
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            border: 1px solid #ddd;
            overflow: auto;
            max-height: 300px;
        }
        .btn { 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            margin: 5px;
        }
        .btn-primary { background: #47866A; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .status { padding: 5px 10px; border-radius: 3px; }
        .status-active { background: #d4edda; color: #155724; }
        .status-none { background: #fff3cd; color: #856404; }
        .status-disabled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>🔧 Отладка сессии Laravel + PHP</h1>

    <!-- PHP сессия -->
    <div class="debug-section">
        <h2 class="debug-title">1. PHP сессия</h2>
        <?php
        // Запускаем PHP сессию
        $phpSessionStatus = session_status();
        $sessionStatusText = '';
        switch($phpSessionStatus) {
            case PHP_SESSION_ACTIVE: $sessionStatusText = '<span class="status status-active">ACTIVE</span>'; break;
            case PHP_SESSION_NONE: $sessionStatusText = '<span class="status status-none">NONE</span>'; break;
            case PHP_SESSION_DISABLED: $sessionStatusText = '<span class="status status-disabled">DISABLED</span>'; break;
        }
        
        // Сохраняем тестовые данные
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['php_test_time'] = date('Y-m-d H:i:s');
        $_SESSION['php_random'] = rand(1000, 9999);
        ?>
        
        <p><strong>Статус:</strong> <?php echo $sessionStatusText; ?> (код: <?php echo $phpSessionStatus; ?>)</p>
        <p><strong>ID сессии:</strong> <?php echo session_id(); ?></p>
        <p><strong>Имя сессии:</strong> <?php echo session_name(); ?></p>
        <p><strong>Save path:</strong> <?php echo ini_get('session.save_path'); ?></p>
        
        <h3>Данные PHP сессии:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>

    <!-- Laravel сессия -->
    <div class="debug-section">
        <h2 class="debug-title">2. Laravel сессия</h2>
        @php
            // Получаем ID сессии Laravel
            $laravelSessionId = session()->getId();
            $laravelSessionData = session()->all();
            
            // Добавляем тестовые данные
            session(['laravel_test_time' => now()->toDateTimeString()]);
            session(['laravel_random' => rand(1000, 9999)]);
            session()->save();
        @endphp
        
        <p><strong>ID сессии:</strong> {{ $laravelSessionId }}</p>
        <p><strong>Драйвер:</strong> {{ config('session.driver') }}</p>
        
        <h3>Данные Laravel сессии:</h3>
        <pre>@php print_r($laravelSessionData); @endphp</pre>
        
        <h3>Проверка Auth:</h3>
        <pre>@php 
            echo "Auth::check(): " . (auth()->check() ? 'true' : 'false') . "\n";
            echo "Auth::id(): " . auth()->id() . "\n";
            if(auth()->check()) {
                echo "User: " . auth()->user()->username . "\n";
            }
        @endphp</pre>
    </div>

    <!-- Куки браузера -->
    <div class="debug-section">
        <h2 class="debug-title">3. Куки браузера</h2>
        <pre>@php print_r($_COOKIE); @endphp</pre>
    </div>

    <!-- Тестовые действия -->
    <div class="debug-section">
        <h2 class="debug-title">4. Тестовые действия</h2>
        
        <div style="margin-bottom: 15px;">
            <form method="POST" action="{{ route('debug.session.test') }}">
                @csrf
                <input type="text" name="test_data" placeholder="Тестовые данные" style="padding: 8px; width: 300px;">
                <button type="submit" class="btn btn-success">Сохранить в обе сессии</button>
            </form>
        </div>
        
        <div style="margin-bottom: 15px;">
            <form method="POST" action="{{ route('debug.session.login') }}">
                @csrf
                <input type="text" name="username" placeholder="Имя пользователя" style="padding: 8px;">
                <button type="submit" class="btn btn-primary">Создать тестовый вход</button>
            </form>
        </div>
        
        <div>
            <form method="POST" action="{{ route('debug.session.clear') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Очистить все сессии</button>
            </form>
        </div>
    </div>

    <!-- Информация о сервере -->
    <div class="debug-section">
        <h2 class="debug-title">5. Информация о сервере</h2>
        <p><strong>PHP Version:</strong> {{ phpversion() }}</p>
        <p><strong>Laravel Version:</strong> {{ app()->version() }}</p>
        <p><strong>Session Driver:</strong> {{ config('session.driver') }}</p>
        <p><strong>Cache Driver:</strong> {{ config('cache.default') }}</p>
    </div>

    <!-- Ссылки для проверки -->
    <div class="debug-section">
        <h2 class="debug-title">6. Проверка работы</h2>
        <p><a href="{{ url()->current() }}" class="btn">Обновить страницу</a></p>
        <p><a href="/" target="_blank" class="btn">Открыть главную</a> (проверить сессию там)</p>
        <p><a href="/auth" target="_blank" class="btn">Открыть страницу авторизации</a></p>
    </div>
</body>
</html>