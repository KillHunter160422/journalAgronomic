@extends('sample.main')

@section('header-title')
Редактировать профиль
@endsection

@section('content')
<div class="edit-profile-container">
    <div class="back-nav">
        <a href="{{ route('profile.show') }}" class="back-link">← Назад к профилю</a>
    </div>
    
    <h1>Редактировать профиль</h1>
    
    <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
        @csrf
        
        <div class="form-section">
            <h3>Основная информация</h3>
            
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" value="{{ $user->username }}" disabled>
                <small class="hint">Имя пользователя нельзя изменить</small>
            </div>
            
            <div class="form-group">
                <label for="fullname">Полное имя</label>
                <input type="text" id="fullname" name="fullname" 
                       value="{{ $user->fullname ?? '' }}"
                       placeholder="Введите ваше полное имя">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" 
                       value="{{ $user->email }}" required>
            </div>
            
            <div class="form-group">
                <label for="avatar">Загрузить аватар</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                <small class="hint">Максимальный размер: 2MB. Форматы: JPG, PNG, GIF</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Смена пароля</h3>
            <small class="section-hint">Заполняйте только если хотите сменить пароль</small>
            
            <div class="form-group">
                <label for="current_password">Текущий пароль</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            
            <div class="form-group">
                <label for="new_password">Новый пароль</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            
            <div class="form-group">
                <label for="new_password_confirmation">Подтвердите новый пароль</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation">
            </div>
        </div>
        
        <div class="form-actions">
            <a href="{{ route('profile.show') }}" class="btn-cancel">Отмена</a>
            <button type="submit" class="btn-submit">Сохранить изменения</button>
        </div>
    </form>
</div>

<style>
.edit-profile-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 30px 20px;
}

.back-nav {
    margin-bottom: 20px;
}

.back-link {
    color: #47866A;
    text-decoration: none;
    font-size: 14px;
}

.back-link:hover {
    text-decoration: underline;
}

h1 {
    color: #333;
    margin: 0 0 30px 0;
    font-size: 28px;
}

.profile-form {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.section-hint {
    display: block;
    color: #666;
    font-size: 13px;
    margin-bottom: 20px;
    font-style: italic;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #47866A;
    box-shadow: 0 0 0 2px rgba(71, 134, 106, 0.1);
}

.form-group input:disabled {
    background: #f8f9fa;
    color: #666;
    cursor: not-allowed;
}

.hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
    font-style: italic;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-submit, .btn-cancel {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
}

.btn-submit {
    background: linear-gradient(90deg, #47866A, #5CA08A);
    color: white;
    font-weight: bold;
}

.btn-submit:hover {
    background: linear-gradient(90deg, #3a7557, #4A8C74);
    transform: translateY(-2px);
}

.btn-cancel {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-cancel:hover {
    background: #e9ecef;
    color: #333;
}

@media (max-width: 768px) {
    .profile-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endsection