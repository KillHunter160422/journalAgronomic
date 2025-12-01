@extends('sample.main')

@section('header-title')
Журнал
@endsection

@section('content')
    <h3>Журнал наблюдений</h3>
    <div class="container">
    <div class="card">
        <div class="crop-name">
            <div>
                <h3>Avatar</h3>
            </div>
            <div>
                <p>username</p>
                <p>Описание поля(до 100 символов)</p>
                <p>Название культуры</p>
                <p>Сорт</p>
                <p>Вегетационный период</p>
            </div>
            <div class="field-stats">
                <p>Площадь поля</p>
                <p>Кол-во операций</p>
            </div>
            <div>Дата публикации/обновления</div>
        </div>
    </div>

    <style>
        p, h3 {
            text-align: center;
        }
        p{
            margin: 10px;
            text-align: center;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
            margin: 10px;
        }
        .card{
            display: flex;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            background-color: #e0e0e0;
        }
        .crop-name{
            padding: 20px;
            align-items: center;
            text-align: center;
        }
        .field-stats {
            display: flex;
            gap: 15px;
            font-size: 14px;
}
    </style>
@endsection