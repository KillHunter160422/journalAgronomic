<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header-title')</title>
    @yield('integratedLink')
</head>
<body class="m-0 p-0 min-h-screen">
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            background: linear-gradient(0deg, #D2B48C 10%, #A8DADC 24%);
            min-height: 100vh;
            font-family: 'Roboto', Arial, sans-serif;
            overflow-x: hidden;
        }
        header {
            width: 100vw;
            min-height: 70px;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #47866A 5%, #D2B48C 15%);
            border-bottom: 2px solid #E0E0E0;
            position: relative;
            flex-wrap: nowrap;
            gap: 20px;
            z-index: 100;
        }
        nav{
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 0;
        }
        .menu-list {
            list-style: none; 
            display: flex;
            flex-wrap: nowrap;
            width: 100%;
            gap: 30px;
            padding: 0;
            margin: 0;
            justify-content: center;
            align-items: center;
        }
        .menu-list li {
            display: inline-block;
        }
        .menu-list a{
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            padding: 8px 0;
            white-space: nowrap;
            transition: color 0.3s ease;
        }
        .menu-list a:hover{
            color: #47866A;
        }

        .email {
            flex-shrink: 0;
            white-space: nowrap;
        }

        .email a{
            font-size: 14px;
            color: white;
            text-decoration: none;
        }

        .auth{
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .logo{
            flex-shrink: 0;
        }
        .logo a{
            display: flex;
            align-items: center;
        }

        .user-menu{
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar{
            width: 40px;
            height: 40px;
            border-radius: 40%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #D2B48C;            
        }

        .dropdown-menu{
            position: absolute;
            right: 0;
            top: 100%;
            background: #D2B48C;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            display: none;
        }

        .dropdown-menu.show{
            display: block;
        }

        .dropdown-item{
            padding: 12px 15px;
            display: block;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .dropdown-item:hover{
            background: #47866A;
        }

        .dropdown-item:last-child{
            border-bottom: none;
        }

        .auth-buttons{
            display: flex;
            gap: 10px;
        }

        .btn{
            padding: 8px 16px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: white;
            transition: color 0.3s ease;
        }

        .btn:hover{
            color:  #47866A;
        }

        .logout-btn{
            background: none;
            border: none;
            color: #dc3541;
            cursor: pointer;
            width: 100%;
        }

        .user-info{
            color: white;
            font-weight: bold;
            margin-right: 10px;
            white-space: nowrap;
        }

    </style>
        <header>
        <div class="logo flex-shrink-0">
            <a href="/">
                <img src="https://svgsilh.com/svg/1994653.svg" width="48" height="48" alt="Logo">
            </a>
        </div>
        
        <nav class="menu flex-grow flex justify-center"> 
        <?php
            $itemMenu=[
                ["url" => "/", "text" => "Главная"],
                ["url" => "/my_field", "text" => "Мои поля"],
                ["url" => "/journal", "text" => "Журнал"]
        ];
        foreach($itemMenu as $item){
            echo " <ul class=\"menu-list flex flex-row flex-nowrap m-0 p-0 list-none gap-12\">
                <li>
                    <a href=\"{$item['url']}\">
                        {$item['text']}</a>
                </li>
                </ul>";
        }
        
        ?>
        </nav>
        
        <div class="email">
            <a href="mailto:today5388@gmail.com">support@journalObservation.ru</a>
        </div>
<div class="auth">
    <?php
    $isLoggedIn = php_auth_check();
    $user = php_auth_user();
    
    if ($isLoggedIn && $user): 
        // Получаем роли через прямой SQL запрос
        $roles = \DB::table('user_role_assignments')
            ->join('user_roles', 'user_role_assignments.role_id', '=', 'user_roles.role_id')
            ->where('user_role_assignments.user_id', $user['id'])
            ->pluck('user_roles.role_name')
            ->toArray();
    ?>
        <div class="user-menu">
            <span class="user-info">
                <?php echo htmlspecialchars($user['username']); ?>
                
                <?php 
                // Отображаем роли
                if (!empty($roles)): 
                    foreach ($roles as $role): 
                        // Определяем класс для бейджа
                        $badgeClass = 'role-badge';
                        if ($role === 'admin') {
                            $badgeClass .= ' admin';
                        } elseif ($role === 'moderator') {
                            $badgeClass .= ' moderator';
                        }
                        
                        // Получаем иконку и название роли
                        $icon = php_auth_role_icon($role);
                        $label = php_auth_role_label($role);
                ?>
                        <span class="<?php echo $badgeClass; ?>">
                            <?php echo $icon; ?> <?php echo htmlspecialchars($label); ?>
                        </span>
                <?php 
                    endforeach;
                endif; 
                ?>
            </span>
            <img src="<?php echo $user['avatar_url'] ? htmlspecialchars($user['avatar_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=47866A&color=fff&size=40'; ?>" 
                alt="Аватар" 
                class="user-avatar"
                onclick="toggleMenu()">
            
            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-item">
                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                </div>
                <div class="dropdown-item">
                    <?php echo htmlspecialchars($user['email']); ?>
                </div>
                
                <?php if (!empty($roles)): ?>
                <div class="dropdown-item">
                    <small>Роли:</small>
                    <div style="margin-top: 5px;">
                        <?php foreach ($roles as $role): ?>
                            <span style="font-size: 11px; background: #f0f0f0; padding: 2px 6px; border-radius: 10px; margin-right: 3px;">
                                <?php echo php_auth_role_icon($role); ?> <?php echo php_auth_role_label($role); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="dropdown-item">
                    <a href="<?php echo route('profile.show'); ?>"  style="color: inherit; text-decoration: none;">
                        ⚙️ Профиль
                    </a>
                </div>
                <?php if (in_array('admin', $roles)): ?>
                <div class="dropdown-item">
                    <a href="<?php echo route('admin.dashboard'); ?>" style="color: inherit; text-decoration: none;">
                        🛡️ Админ-панель
                    </a>
                </div>
                <?php endif; ?>
                <div class="dropdown-item">
                    <form method="POST" action="<?php echo route('logout'); ?>" style="margin: 0;">
                        <input type="hidden" name="_token" value="<?php echo php_session('_token'); ?>">
                        <button type="submit" class="logout-btn">
                            🚪 Выйти
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="auth-buttons">
            <a href="/auth" class="btn">Войти</a>
        </div>
    <?php endif; ?>
</div>
    </header>
    <script>
        function toggleMenu() {
            document.getElementById('userDropdown').classList.toggle('show');
        }
        
        // Закрыть меню при клике вне его
        window.addEventListener('click', function(e) {
            if (!e.target.matches('.user-avatar') && !e.target.matches('.user-info')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
    @yield('content')
</body>
</html>