<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header-title')</title>
    @yield('integratedLink')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
            width: 100%;
            min-height: 70px;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #47866A 5%, #D2B48C 15%);
            border-bottom: 2px solid #E0E0E0;
            flex-wrap: nowrap;
            gap: 15px;
            z-index: 100;
            position: relative;
        }
        
        /* Мобильное меню */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            z-index: 1001;
        }
        
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .mobile-menu-overlay.active {
            display: block;
        }
        
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: linear-gradient(180deg, #47866A 0%, #D2B48C 100%);
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        }
        
        .mobile-menu.active {
            left: 0;
        }
        
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .mobile-menu-logo {
            width: 40px;
            height: 40px;
        }
        
        .close-menu {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .mobile-menu-list {
            list-style: none;
        }
        
        .mobile-menu-list li {
            margin-bottom: 15px;
        }
        
        .mobile-menu-list a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            display: block;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        
        .mobile-menu-list a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .mobile-auth {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .mobile-user-info {
            color: white;
            margin-bottom: 20px;
        }
        
        .mobile-user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            margin-bottom: 10px;
        }
        
        .mobile-email {
            color: white;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .mobile-logout-btn {
            width: 100%;
            padding: 12px;
            background: rgba(220, 53, 69, 0.2);
            color: white;
            border: 2px solid rgba(220, 53, 69, 0.5);
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Адаптивные стили для разных разрешений */
        @media (min-width: 1920px) {
            header {
                padding: 0 30px;
                gap: 30px;
            }
            
            .menu-list {
                gap: 50px !important;
            }
            
            .menu-list a {
                font-size: 22px !important;
            }
        }
        
        @media (max-width: 1366px) {
            header {
                padding: 0 10px;
                gap: 10px;
                min-height: 60px;
            }
            
            .menu-list {
                gap: 20px !important;
            }
            
            .menu-list a {
                font-size: 18px !important;
                padding: 6px 0 !important;
            }
            
            .logo img {
                width: 40px !important;
                height: 40px !important;
            }
            
            .user-avatar {
                width: 35px !important;
                height: 35px !important;
            }
            
            .user-info {
                font-size: 14px !important;
                margin-right: 8px !important;
            }
        }
        
        @media (max-width: 1024px) {
            .menu-list {
                gap: 15px !important;
            }
            
            .menu-list a {
                font-size: 16px !important;
            }
            
            .email a {
                font-size: 12px !important;
            }
            
            .user-info span.role-badge {
                display: none !important;
            }
        }
        
        /* Мобильная версия */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            nav.menu {
                display: none;
            }
            
            .email {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .logo {
                order: 1;
                flex: 1;
            }
            
            .mobile-menu-toggle {
                order: 2;
            }
            
            .auth {
                order: 3;
            }
            
            .auth-buttons {
                display: none;
            }
            
            .user-menu .user-avatar {
                display: block;
            }
            
            .user-menu .dropdown-menu {
                display: none !important;
            }
            
            header {
                flex-wrap: wrap;
                justify-content: space-between;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none !important;
            }
            
            .mobile-menu {
                display: none !important;
            }
            
            .mobile-menu-overlay {
                display: none !important;
            }
        }
        
        nav {
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }
        
        .menu-list {
            list-style: none;
            display: flex;
            flex-wrap: nowrap;
            gap: 30px;
            padding: 0;
            margin: 0;
            justify-content: center;
            align-items: center;
            flex-shrink: 1;
        }
        
        .menu-list li {
            display: inline-block;
            flex-shrink: 0;
        }
        
        .menu-list a {
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            padding: 8px 0;
            white-space: nowrap;
            transition: color 0.3s ease;
            display: block;
        }
        
        .menu-list a:hover {
            color: #47866A;
        }
        
        .email {
            flex-shrink: 0;
            white-space: nowrap;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .email a {
            font-size: 14px;
            color: white;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .auth {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            min-width: 0;
        }
        
        .logo {
            flex-shrink: 0;
        }
        
        .logo a {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 40%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #D2B48C;
            flex-shrink: 0;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: #D2B48C;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            display: none;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 12px 15px;
            display: block;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .dropdown-item:hover {
            background: #47866A;
            color: white;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: white;
            transition: color 0.3s ease;
            white-space: nowrap;
        }
        
        .btn:hover {
            color: #47866A;
        }
        
        .logout-btn {
            background: none;
            border: none;
            color: #dc3541;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-size: 14px;
        }
        
        .user-info {
            color: white;
            font-weight: bold;
            margin-right: 10px;
            white-space: nowrap;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .role-badge {
            display: inline-block;
            font-size: 11px;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 10px;
            color: #333;
            flex-shrink: 0;
        }
        
        .role-badge.admin {
            background: #ffd700;
        }
        
        .role-badge.moderator {
            background: #87ceeb;
        }
    </style>
    
    <header>
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="logo">
            <a href="/">
                <img src="https://svgsilh.com/svg/1994653.svg" alt="Logo">
            </a>
        </div>
        
        <nav class="menu">
            <ul class="menu-list">
                <?php
                $itemMenu = [
                    ["url" => "/", "text" => "Главная"],
                    ["url" => "/my_field", "text" => "Мои поля"],
                    ["url" => "/journal", "text" => "Журнал"]
                ];
                foreach($itemMenu as $item): ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>">
                            <?php echo $item['text']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
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
                        if (!empty($roles)): 
                            foreach ($roles as $role): 
                                $badgeClass = 'role-badge';
                                if ($role === 'admin') {
                                    $badgeClass .= ' admin';
                                } elseif ($role === 'moderator') {
                                    $badgeClass .= ' moderator';
                                }
                                
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
                            <div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 3px;">
                                <?php foreach ($roles as $role): ?>
                                    <span style="font-size: 11px; background: #f0f0f0; padding: 2px 6px; border-radius: 10px;">
                                        <?php echo php_auth_role_icon($role); ?> <?php echo php_auth_role_label($role); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="dropdown-item">
                            <a href="<?php echo route('profile.show'); ?>" style="color: inherit; text-decoration: none;">
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
    
    <!-- Мобильное меню -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>
    
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <img src="https://svgsilh.com/svg/1994653.svg" alt="Logo" class="mobile-menu-logo">
            <button class="close-menu" onclick="closeMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <ul class="mobile-menu-list">
            <?php
            foreach($itemMenu as $item): ?>
                <li>
                    <a href="<?php echo $item['url']; ?>" onclick="closeMobileMenu()">
                        <?php echo $item['text']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="mobile-email">
            <a href="mailto:today5388@gmail.com">support@journalObservation.ru</a>
        </div>
        
        <div class="mobile-auth">
            <?php if ($isLoggedIn && $user): ?>
                <div class="mobile-user-info">
                    <img src="<?php echo $user['avatar_url'] ? htmlspecialchars($user['avatar_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=47866A&color=fff&size=60'; ?>" 
                         alt="Аватар" 
                         class="mobile-user-avatar">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <?php if (!empty($roles)): ?>
                    <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px;">
                        <?php foreach ($roles as $role): ?>
                            <span style="font-size: 12px; background: rgba(255, 255, 255, 0.2); color: white; padding: 4px 8px; border-radius: 12px;">
                                <?php echo php_auth_role_icon($role); ?> <?php echo php_auth_role_label($role); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <a href="<?php echo route('profile.show'); ?>" style="color: white; text-decoration: none; display: block; padding: 12px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; margin-bottom: 10px;">
                    <i class="fas fa-cog"></i> Профиль
                </a>
                
                <?php if (in_array('admin', $roles)): ?>
                <a href="<?php echo route('admin.dashboard'); ?>" style="color: white; text-decoration: none; display: block; padding: 12px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; margin-bottom: 10px;">
                    <i class="fas fa-shield-alt"></i> Админ-панель
                </a>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo route('logout'); ?>" style="margin: 0;">
                    <input type="hidden" name="_token" value="<?php echo php_session('_token'); ?>">
                    <button type="submit" class="mobile-logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>
            <?php else: ?>
                <a href="/auth" style="color: white; text-decoration: none; display: block; padding: 12px; background: rgba(255, 255, 255, 0.2); border-radius: 8px; text-align: center; font-weight: bold;">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleMenu() {
            if (window.innerWidth > 768) {
                document.getElementById('userDropdown').classList.toggle('show');
            } else {
                toggleMobileMenu();
            }
        }
        
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
            document.getElementById('mobileMenuOverlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('active') ? 'hidden' : '';
        }
        
        function closeMobileMenu() {
            document.getElementById('mobileMenu').classList.remove('active');
            document.getElementById('mobileMenuOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }
        
        window.addEventListener('click', function(e) {
            if (!e.target.matches('.user-avatar') && !e.target.matches('.user-info') && !e.target.matches('.mobile-menu-toggle')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
        
        // Закрытие меню при нажатии Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
        
        // Адаптивное поведение при ресайзе
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
    </script>
    
    @yield('content')
</body>
</html>