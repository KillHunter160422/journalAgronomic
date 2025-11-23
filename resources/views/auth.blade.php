@extends('sample.main')

@section('header-title')
{{ isset($is_register_mode) && $is_register_mode ? 'Регистрация' : 'Авторизация' }}
@endsection

@section('content')
<div class="form-section">
    <h3 class="is_reg_mode">{{ isset($is_register_mode) && $is_register_mode ? 'Регистрация' : 'Авторизация' }}</h3>
    
    @if(session('success'))
        <div class="message success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="message error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="message error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <!-- Форма для основных действий -->
    <form method="POST" action="{{ route('auth.process') }}" id="main-form">
        @csrf
        
        <div class="form-group">
            <label>Логин:{{ (isset($is_register_mode) && $is_register_mode) ? ' *' : '' }}</label>
            <input type="text" name="login" value="{{ old('login') }}" 
                   {{ (isset($is_register_mode) && $is_register_mode) ? 'required' : '' }}>
            @if(!(isset($is_register_mode) && $is_register_mode))
                <small class="field-hint">Минимум 3 символа (только для входа)</small>
            @else
                <small class="field-hint">Минимум 3 символа, максимум 20</small>
            @endif
        </div>
        
        <div class="form-group">
            <label>Пароль:{{ (isset($is_register_mode) && $is_register_mode) ? ' *' : '' }}</label>
            <input type="password" name="password" 
                   {{ (isset($is_register_mode) && $is_register_mode) ? 'required' : '' }}>
            <small class="field-hint">Минимум 6 символов</small>
        </div>
        
        @if(isset($is_register_mode) && $is_register_mode)
        <div id="register-fields">
            <div class="form-group">
                <label>Повторите пароль: *</label>
                <input type="password" name="password_confirmation">
            </div>
            
            <div class="form-group">
                <label>Email: *</label>
                <input type="email" name="email" value="{{ old('email') }}">
            </div>
            
            <div class="form-group">
                <label>ФИО: *</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}">
                <small class="field-hint">Минимум 2 символа</small>
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="agree_terms">
                    Я согласен с условиями использования *
                </label>
            </div>
        </div>
        @endif
        
        <div class="form-buttons">
            @if(!(isset($is_register_mode) && $is_register_mode))
                <button type="submit" name="login_btn" class="btn-primary">Войти</button>
            @else
                <button type="submit" name="register_btn" class="btn-primary">Зарегистрироваться</button>
            @endif
        </div>
    </form>

    <!-- Отдельные формы для переключения -->
    <div class="switch-buttons">
        @if(!(isset($is_register_mode) && $is_register_mode))
            <form method="POST" action="{{ route('auth.process') }}" class="switch-form">
                @csrf
                <input type="hidden" name="show_register" value="1">
                <button type="submit" class="btn-secondary">Зарегистрироваться</button>
            </form>
        @else
            <form method="POST" action="{{ route('auth.process') }}" class="switch-form">
                @csrf
                <input type="hidden" name="show_login" value="1">
                <button type="submit" class="btn-secondary">Назад к авторизации</button>
            </form>
        @endif
    </div>
</div>

<style>
    .form-section {
        max-width: 500px;
        margin: 20px auto;
        padding: 30px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        background: #8B4513;
    }
    .is_reg_mode{
        text-align: center;
        color: #e0e0e0;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #e0e0e0;}
    .form-group input, .form-group select { 
        width: 95%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; 
    }
    .form-group input:invalid { border-color: #e74c3c; }
    .form-group input:valid { border-color: #27ae60; }
    .field-hint { color: #7f8c8d; font-size: 12px; margin-top: 5px; display: block; }
    .btn-primary, .btn-secondary { 
        padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; 
        margin: 5px;
    }
    .btn-primary { background: #055426ff; color: white; }
    .btn-secondary { background: #669b9fff; color: white; }
    .message { padding: 15px; margin: 20px auto; border-radius: 6px; text-align: center; }
    .message.success { background: #d4edda; color: #155724; }
    .message.error { background: #f8d7da; color: #721c24; }
    .switch-buttons { margin-top: 20px; text-align: center; }
    .switch-form { display: flex; }
</style>

<script>
</script>
@endsection