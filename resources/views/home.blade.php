@extends('sample.main')

@section('header-title')
Главная страница
@endsection

@section('content')
    <style>
        body {
            background: linear-gradient(0deg, #D2B48C 10%, #A8DADC 24%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .pros {
            border: 2px solid #D2B48C;
            border-radius: 15%;
            background-color: #ffe5d6;
            padding: 30px;
            margin: 0px;
            max-width: 800px;
            width: 80%;
            text-align: left;
        }
        .menu a{
            font-weight: bold;
        }
        .menu-list a {
            transition: color 0.3s ease;
            margin: 80px;
        }
        .centered-title {
            text-align: center;
            margin: 40px 0 20px 0;
            width: 100%;
        }
        .centered-title h2 {
            display: inline-block;
            margin: 0;
            font-size: 50px;
            font-weight: bold;
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
            <ul class="menu-list flex flex-row flex-nowrap m-0 p-0 list-none gap-12">
                <li>
                    <a class="px-4 py-3 no-underline text-white" href="/">Home</a>
                </li>
                <li>
                    <a class="px-4 py-3 no-underline text-white " href="">About us</a>
                </li>
                <li>
                    <a class="px-4 py-3 no-underline text-white" href="/journal">Journal</a>
                </li>
            </ul>
        </nav>
        
        <div class="email flex-shrink-0">
            <a href="mailto:today5388@gmail.com" class="text-white hover:text-gray-200 transition-colors no-underline">support@journalObservation.ru</a>
        </div>
        
        <div class="auth flex-shrink-0">
            <a href="/auth" class="px-4 py-2 no-underline text-white font-bold hover:text-gray-200 transition-colors bg-green-700 rounded-lg">Sign In</a>
        </div>
    </header>

    <!-- Заголовок по центру -->
    <div class="centered-title">
        <h2 class="">Почему стоит выбрать нас?</h2>
    </div>

    <!-- Блок под заголовком -->
    <div class="pros">
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
    
@endsection