<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header-title')</title>
    @yield('integratedLink')
</head>
<body class="m-0 p-0 min-h-screen">
    <style>
        body {
            background: linear-gradient(0deg, #D2B48C 10%, #A8DADC 24%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
        }
        header {
            width: 100vw;
            height: 30px;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            gap: 32px;
            background: linear-gradient(90deg, #47866A 5%, #D2B48C 15%);
            border-bottom: 2px solid #E0E0E0;
        }
                nav{
            display: flex;
            flex-grow: 0.5;
            justify-content: center;
        }
        .menu-list {
            font-size: 20px;
            list-style: none; 
            padding: 0;
            margin: auto;
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            flex-direction: row;
        }
        .menu-list a, .email a, .auth a{
            display: flex;
            font-weight: bold;
            margin: 80px;
            text-decoration: none;
            color: white;
            flex-grow: 1;
            justify-content: center;
        }
        .auth a{
            font-size: 20px;
        }
        .menu-list a:hover, .auth a:hover {
            transition: color 0.5s ease;
            font-weight: bold;
            color: #47866A;
        }
        .logo a{
            display: flex;
            flex-shrink: 0;
            margin-left: 20px;
        }
    </style>
        <header>
        <div class="logo flex-shrink-0">
            <a href="/">
                <img src="https://belikova.ru/assets/images/icons/mir.svg" width="48" height="48" alt="Logo">
            </a>
        </div>
        
        <nav class="menu flex-grow flex justify-center"> 
        <?php
            $itemMenu=[
                ["url" => "/", "text" => "Home"],
                ["url" => "/forum", "text" => "Forum"],
                ["url" => "/", "text" => "Journal"]
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
            <a href="/auth">Login</a>
        </div>
    </header>
    @yield('content')
</body>
</html>