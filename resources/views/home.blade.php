@extends('sample.main')

@section('header-title')
Главная страница
@endsection

@section('integratedLink')
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
@endsection

@section('content')
    <style>
        /* Основные стили */
        .benefits-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-info {
            font-family: 'Lato', Arial, sans-serif;
            text-align: center;
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .info {
            display: grid;
            grid-template-columns: 1.2fr 1fr; /* Увеличил первую колонку, уменьшил вторую */
            gap: 30px;
            box-sizing: border-box;
        }

        .pros, .pros-2, .pros-3 {
            border: 2px solid #D2B48C;
            border-radius: 25px;
            background-color: #ffe5d6;
            padding: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(210, 180, 140, 0.1);
        }

        .pros-3 {
            grid-column: 1;
            grid-row: 1 / span 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .pros {
            grid-column: 2;
            grid-row: 1;
            min-height: auto;
        }

        .pros-2 {
            grid-column: 2;
            grid-row: 2;
            margin-top: 0;
            min-height: auto;
        }

        .pros:hover, .pros-2:hover, .pros-3:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(210, 180, 140, 0.2);
        }

        /* Подзаголовки по центру */
        .pros h4, .pros-2 h4, .pros-3 h4 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
            line-height: 1.3;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #D2B48C;
        }

        /* Основной текст более серый */
        .pros p, .pros-2 p, .pros-3 p {
            color: #666666; /* Более серый цвет текста */
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        /* Элементы списка тоже серые */
        .pros ul {
            padding-left: 18px;
            margin: 15px 0;
        }

        .pros li {
            margin-bottom: 8px;
            color: #666666; /* Более серый цвет для списка */
            line-height: 1.5;
            font-size: 15px;
        }

        /* Сделаем правые блоки более компактными */
        .pros, .pros-2 {
            padding: 20px;
        }

        .pros h4, .pros-2 h4 {
            font-size: 18px;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        .pros p, .pros-2 p {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* Особый стиль для первого параграфа в pros-3 */
        .pros-3 p:first-of-type {
            margin-top: 10px;
            font-style: italic;
            color: #555555;
        }

        /* Адаптивные стили */
        @media (max-width: 1024px) {
            .info {
                grid-template-columns: 1.5fr 1fr; /* Сохраняем пропорции на планшетах */
                gap: 25px;
            }
            
            .pros-3 {
                grid-column: 1;
                grid-row: 1;
            }
            
            .pros {
                grid-column: 2;
                grid-row: 1;
            }
            
            .pros-2 {
                grid-column: 1 / span 2;
                grid-row: 2;
                margin-top: 25px;
            }
            
            .pros, .pros-2 {
                padding: 18px;
            }
        }

        @media (max-width: 768px) {
            .benefits-wrapper {
                padding: 15px;
            }
            
            .header-info {
                font-size: 28px;
                margin-bottom: 30px;
            }
            
            .info {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .pros-3, .pros, .pros-2 {
                grid-column: 1;
                grid-row: auto;
            }
            
            .pros-2 {
                margin-top: 0;
            }
            
            .pros h4, .pros-2 h4, .pros-3 h4 {
                font-size: 18px;
            }
            
            .pros p, .pros-2 p, .pros-3 p {
                font-size: 14px;
            }
            
            .pros, .pros-2, .pros-3 {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .header-info {
                font-size: 24px;
                margin-bottom: 25px;
            }
            
            .pros h4, .pros-2 h4, .pros-3 h4 {
                font-size: 17px;
                padding-bottom: 6px;
            }
            
            .pros p, .pros-2 p, .pros-3 p {
                font-size: 13px;
            }
            
            .pros, .pros-2, .pros-3 {
                padding: 18px;
                border-radius: 20px;
            }
            
            .pros ul {
                padding-left: 15px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .info {
                grid-template-columns: 1.5fr 1fr;
            }
        }
    </style>

    <div class="benefits-wrapper">
        <div class="header-info">
            <h2>Почему стоит выбрать нас?</h2>
        </div>
        
        <section class="info">
            <div class="pros-3">
                <p>
                    Мы не просто предлагаем еще один цифровой инструмент — мы создали решение, которое выращено из реальных потребностей 
                    таких же аграриев и энтузиастов, как и вы. Вот что делает наш сервис по-настоящему ценным:
                </p>
                <h4>Создан практиками, чтобы помочь практикам</h4>
                <p>
                    Наша команда много лет работает в поле, и мы на собственном опыте столкнулись с неудобством бумажных записей, которые
                    легко теряются и не дают целостной картины. Этот журнал — это наш ответ на вызовы, с которыми сталкивается каждый
                    растениевод. Здесь учтены все мелочи, важные для ежедневной работы.
                </p>
                <h4>Без скрытых платежей и ограничений</h4>
                <p>
                    Мы верим, что знания и инструменты для эффективного земледелия должны быть доступны всем. Вы получаете не урезанную 
                    демо-версию, а полнофункциональный журнал с пожизненным бесплатным доступом ко всем возможностям.
                </p>
                <h4>Ваш личный опыт — это главная ценность</h4>
                <p>
                    Вы — не просто пользователь, вы — соавтор нашей общей базы знаний. Добавляйте свои культуры, описывайте тонкости ухода,
                    делитесь лайфхаками и оставляйте отзывы о сортах. Это помогает создать живое сообщество, где каждый может учиться на успехах
                    и ошибках других.
                </p>
                <h4>Экспертное сообщество и обратная связь</h4>
                <p>
                    Присоединяясь к нам, вы получаете доступ не только к инструменту, но и к коллективному разуму сообщества. Обсуждайте 
                    проблемы, задавайте вопросы и получайте советы от таких же увлеченных людей, что особенно ценно для начинающих фермеров.
                </p>
            </div>
            
            <div class="pros">
                <h4>Единое пространство для всех данных</h4>
                <p>
                    Забудьте о ворохе бумажек, фото в памяти телефона и заметках в разных блокнотах. В нашем журнале 
                    вы можете:
                </p>
                <ul>
                    <li>Вести детальные записи по каждой культуре.</li>
                    <li>Хранить всю историю ухода за растениями в одном месте.</li>
                    <li>Делиться или находить информацию по культуре.</li>
                </ul>
            </div>
            
            <div class="pros-2">
                <h4>Максимальная простота и удобство</h4>
                <p>Мы создали интуитивно понятный журнал, в котором легко и быстро разберется каждый. Вам больше не 
                    придется тратить время на расшифровку бумажных записей и поиск нужной информации — все данные 
                    упорядочены и всегда под рукой.</p>
                <h4>Ваши данные в полной безопасности</h4>
                <p>Все ваши записи надежно хранятся и защищены. Вы больше не рискуете потерять их, как это часто бывает 
                    с бумажными блокнотами
                </p>
            </div>
        </section>
    </div>
@endsection