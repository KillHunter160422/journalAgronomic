@extends('sample.main')

@section('header-title')
Главная страница
@endsection
@section('integratedLink')
    @vite(["resources/css/app.css", "resources/js/app.js"])
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
@endsection
@section('content')
    <style>
        body {
            background: linear-gradient(0deg, #D2B48C 10%, #A8DADC 24%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
        }
        header {
            width: 100%;
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
        .pros {
            grid-column: 2;
            border: 2px solid #D2B48C;
            border-radius: 5cap;
            background-color: #ffe5d6;
            padding: 30px;
            margin: 0;
            max-width: 800px;
        }
        .pros-2{
            grid-column: 2;
            border: 2px solid #D2B48C;
            border-radius: 5cap;
            background-color: #ffe5d6;
            padding: 30px;
            margin-top: 20px;
            max-width: 800px;
        }
        .pros-3{
            grid-column: 1;
            grid-row: 1/3;
            border: 2px solid #D2B48C;
            border-radius: 5cap;
            background-color: #ffe5d6;
            padding: 30px;
            margin-right: 20px;
            max-height: 800px;
            max-width: 700px;
        }
        .menu-list {
            list-style: none; 
            padding: 0;
            margin: auto;
            display: flex;
            justify-content: center;
            font-size: 20px;
        }
        .menu-list a {
            transition: color 0.3s ease;
            font-weight: bold;
            margin: 85px;
        }

        .info{
            display: grid;
            box-sizing: border-box;
            padding: 24px;
        }
        .header-info{
            font-family: 'Lato', Arial, sans-serif;
            text-align: center;
            font-size: 30px;
        }
    </style>

    <header class="w-full h-auto m-0 px-5 py-4 flex flex-row flex-nowrap justify-between items-center gap-8" 
    style="background: linear-gradient(90deg, #47866A 5%, #D2B48C 15%); border-bottom: 2px solid #E0E0E0;">
        <div class="logo flex-shrink-0">
            <a href="/">
                <img src="https://belikova.ru/assets/images/icons/mir.svg" width="48" height="48" alt="Logo">
            </a>
        </div>
        
        <nav class="menu flex-grow flex justify-center"> 
        <?php
            $itemMenu=[
                ["url" => "/", "text" => "Home"],
                ["url" => "/about", "text" => "About"],
                ["url" => "/", "text" => "Journal"]
        ];
        foreach($itemMenu as $item){
            echo " <ul class=\"menu-list flex flex-row flex-nowrap m-0 p-0 list-none gap-12\">
                <li>
                    <a class=\"px-4 py-3 no-underline text-white font-bold\" href=\"{$item['url']}\">
                        {$item['text']}</a>
                </li>
                </ul>";
        }
        
        ?>
            

            </ul>
        </nav>
        
        <div class="email flex-shrink-0">
            <a href="mailto:today5388@gmail.com" class="text-white hover:text-gray-200 transition-colors no-underline">support@journalObservation.ru</a>
        </div>
        <div class="auth flex-shrink-0">
            <a href="/auth" class="px-4 py-2 no-underline text-white font-bold hover:text-gray-200 transition-colors bg-green-700 rounded-lg">Sign In</a>
        </div>
    </header>

    <div class="header-info">
        <h2 class="">Почему стоит выбрать нас?</h2>
    </div>
    
    <section class="info">
        
        <div class="pros w-full max-w-[800px] mx-auto my-6 p-8 box-border">
            <p class="text-gray-700 leading-relaxed mb-4">
                Наша группа давно занимается сельскохозяйственными работами, однако каждый раз записывать на бумажку 
                наблюдения стало долго! Чтобы избавиться от рутинной задачи мы решили создать свой журнал наблюдений и добавлять 
                туда данные о тех и/или иных культурах.
            </p>
            <p class="text-gray-700 leading-relaxed mb-6">
                В частности как ухаживать, сколько длиться вегатационный период,
                какие культуры лучше, а какие покупать не стоит!
            </p>
            
            <p class="text-gray-800 font-semibold mb-4">Нашим преимуществом является:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                <li>Бесплатное использование сайта</li>
                <li>Вы можете добавлять свои культуры и рассказывать о них</li>
                <li>Смотреть статистику по выращенным культурам</li>
                <li>Добавлять медиа-материалы</li>
            </ul>
        </div>
        <div class="pros-2">
            <p class="text-gray-700 leading-relaxed mb-4">
                Наша группа давно занимается сельскохозяйственными работами, однако каждый раз записывать на бумажку 
                наблюдения стало долго! Чтобы избавиться от рутинной задачи мы решили создать свой журнал наблюдений и добавлять 
                туда данные о тех и/или иных культурах.
            </p>
            <p class="text-gray-700 leading-relaxed mb-6">
                В частности как ухаживать, сколько длиться вегатационный период,
                какие культуры лучше, а какие покупать не стоит!
            </p>
            
            <p class="text-gray-800 font-semibold mb-4">Нашим преимуществом является:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                <li>Бесплатное использование сайта</li>
                <li>Вы можете добавлять свои культуры и рассказывать о них</li>
                <li>Смотреть статистику по выращенным культурам</li>
                <li>Добавлять медиа-материалы</li>
            </ul>
        </div>
        <div class="pros-3 w-full max-w-[800px] mx-auto my-6 p-8 box-border">
            <p class="text-gray-700 leading-relaxed mb-4">
                Наша группа давно занимается сельскохозяйственными работами, однако каждый раз записывать на бумажку 
                наблюдения стало долго! Чтобы избавиться от рутинной задачи мы решили создать свой журнал наблюдений и добавлять 
                туда данные о тех и/или иных культурах.
            </p>
            <p class="text-gray-700 leading-relaxed mb-6">
                В частности как ухаживать, сколько длиться вегатационный период,
                какие культуры лучше, а какие покупать не стоит!
            </p>
            
            <p class="text-gray-800 font-semibold mb-4">Нашим преимуществом является:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                <li>Бесплатное использование сайта</li>
                <li>Вы можете добавлять свои культуры и рассказывать о них</li>
                <li>Смотреть статистику по выращенным культурам</li>
                <li>Добавлять медиа-материалы</li>
            </ul>
        </div>
    </section>
@endsection