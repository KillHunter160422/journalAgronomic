@extends('sample.main')

@section('header-title')
Журнал
@endsection

@section('content')
    <h3>Журнал наблюдений</h3>
    <div class="container">
        <div class="crops">

        </div>
        <div class="crop-name">
            <h3>Название культуры</h3>
        </div>
        <div class="variety">
            <h3>Сорт</h3>
        </div>
        <div class="vegetation">
            <h3>Вегетационный период</h3>
        </div>
    </div>

    <style>
        h3 {
            text-align: center;
        }
        .container {
            background-color: #e0e0e0;
            min-height: 500px;
            min-width: 800px;
            padding: 10px;
            margin: 20px;
            display: grid;
        }
        .crops {
            background-color: #ff0080ff;
            max-height: 100px;
            max-width: 100px;
            grid-column: 1;

            margin-right: 20px;
        }
        .crop-name {
            grid-column: 2;
        }
        .variety {
            grid-column: 3;
        }
        .vegetation {
            grid-column: 4;
        }
    </style>
@endsection