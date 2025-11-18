<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header-title')</title>

    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="m-0 p-0 min-h-screen">
    @yield('content')
</body>
</html>