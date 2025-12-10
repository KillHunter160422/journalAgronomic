@extends('sample.main')

@section('header-title')
Журнал наблюдений
@endsection

@section('content')
<div class="journal-container">
    <div class="journal-header">
        <h1>Журнал наблюдений</h1>
        <div class="journal-controls">
            <button class="btn-add-field" onclick="location.href='/fields/add'">
                <span>+</span> Добавить поле
            </button>
            <div class="filter-controls">
                <select class="filter-select">
                    <option value="all">Все культуры</option>
                    <option value="wheat">Пшеница</option>
                    <option value="corn">Кукуруза</option>
                    <option value="soy">Соя</option>
                    <option value="barley">Ячмень</option>
                </select>
                <select class="filter-select">
                    <option value="newest">Сначала новые</option>
                    <option value="oldest">Сначала старые</option>
                    <option value="operations">По количеству операций</option>
                </select>
            </div>
        </div>
    </div>

    <div class="fields-container">
        <?php
        use App\Models\Field;
        
        $currentUserId = php_auth_check() ? php_session('user_id') : null;
        
        try {
            // Получаем ТОЛЬКО публичные поля
            $fields = \App\Models\Field::with('user')
                ->where('is_public', 1)
                ->orderBy('updated_at', 'desc')
                ->limit(12)
                ->get();
            
            if ($fields->isEmpty()): ?>
                <div class="no-fields">
                    <p>Пока нет публичных полей для наблюдения</p>
                    <?php if ($currentUserId): ?>
                    <p style="margin-top: 10px;">
                        <a href="{{ route('my-fields.show') }}" style="color: #47866A; text-decoration: underline;">
                            Посмотреть свои поля
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
            <?php else: 
                foreach ($fields as $field):
                    $updatedDate = date('d.m.Y', strtotime($field->updated_at));
                    $createdDate = date('d.m.Y', strtotime($field->created_at));
                    
                    $cropInfo = $field->crop_info;
                    $cropName = $cropInfo['crop_name'] ?? null;
                    $variety = $cropInfo['variety'] ?? null;
                    $vegetationPeriod = $cropInfo['vegetation_period'] ?? null;
                    
                    $status = $field->status;
                    $statusClass = getStatusClass($status);
                    $statusText = getStatusText($status);
                    
                    $cropColor = getCropColor($cropName ?? 'default');
        ?>
        
        <div class="field-card" onclick="location.href='/fields/<?php echo $field->field_id; ?>'">
            <div class="field-header" style="background: <?php echo $cropColor; ?>;">
                <div class="field-avatar">
                    <?php
                    $avatarUrl = $field->user->avatar_url ?? null;
                    $username = $field->user->username ?? 'Пользователь';
                    ?>
                    <img src="<?php echo $avatarUrl ? htmlspecialchars($avatarUrl) : 'https://ui-avatars.com/api/?name=' . urlencode($username) . '&background=47866A&color=fff&size=80'; ?>" 
                         alt="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="field-user-info">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p class="field-name"><?php echo htmlspecialchars($field->field_name ?? 'Без названия'); ?></p>
                </div>
            </div>
            
            <div class="privacy-badge">
                <span class="public-badge" title="Публичное поле">
                    🌍 Публичное
                </span>
            </div>
            
            <div class="field-body">
                <div class="crop-info">
                    <?php if ($cropName): ?>
                    <div class="info-item">
                        <span class="label">Культура:</span>
                        <span class="value"><?php echo htmlspecialchars($cropName); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($variety): ?>
                    <div class="info-item">
                        <span class="label">Сорт:</span>
                        <span class="value"><?php echo htmlspecialchars($variety); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($vegetationPeriod): ?>
                    <div class="info-item">
                        <span class="label">Вегетационный период:</span>
                        <span class="value"><?php echo htmlspecialchars($vegetationPeriod); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="field-stats">
                    <div class="stat-item">
                        <span class="stat-icon">📏</span>
                        <div>
                            <span class="stat-value"><?php echo $field->field_area ?? 0; ?> га</span>
                            <span class="stat-label">Площадь</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">⚙️</span>
                        <div>
                            <span class="stat-value"><?php echo $field->operations_count ?? 0; ?></span>
                            <span class="stat-label">Операций</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">📊</span>
                        <div>
                            <span class="stat-value"><?php echo $field->surveys_count ?? 0; ?></span>
                            <span class="stat-label">Обследований</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field-footer">
                <div class="dates">
                    <span class="date-label">Создано: <?php echo $createdDate; ?></span>
                    <span class="date-label">Обновлено: <?php echo $updatedDate; ?></span>
                </div>
                <div class="field-status">
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php 
                endforeach;
            endif;
            
        } catch (\Exception $e) { ?>
            <div class="error-message">
                <p>Ошибка загрузки данных. Пожалуйста, попробуйте позже.</p>
                <p style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
        <?php } ?>
    </div>
    
    <?php if (isset($fields) && count($fields) >= 12): ?>
    <div class="load-more-container">
        <button class="load-more-btn" onclick="loadMoreFields()">
            Показать еще
        </button>
    </div>
    <?php endif; ?>
</div>

<style>
    .journal-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .journal-header {
        margin-bottom: 30px;
    }
    
    .journal-header h1 {
        text-align: center;
        color: #47866A;
        font-size: 28px;
        margin-bottom: 20px;
    }
    
    .journal-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .btn-add-field {
        background: linear-gradient(90deg, #47866A, #5CA08A);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-add-field:hover {
        background: linear-gradient(90deg, #3a7557, #4A8C74);
        transform: translateY(-2px);
    }
    
    .btn-add-field span {
        font-size: 20px;
    }
    
    .filter-controls {
        display: flex;
        gap: 10px;
    }
    
    .filter-select {
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
        color: #333;
        cursor: pointer;
    }
    
    .fields-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .field-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .field-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .field-header {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
        position: relative;
    }
    
    .field-avatar {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
    }
    
    .field-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.3);
    }
    
    .field-user-info {
        flex-grow: 1;
        min-width: 0;
    }
    
    .field-user-info h3 {
        margin: 0 0 5px 0;
        font-size: 18px;
        text-align: left;
    }
    
    .field-name {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        opacity: 0.95;
        text-align: left;
        line-height: 1.4;
    }
    
    .privacy-badge {
        padding: 10px 15px;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #fafafa;
    }
    
    .public-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .field-body {
        padding: 20px;
        flex-grow: 1;
    }
    
    .crop-info {
        margin-bottom: 20px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .label {
        color: #666;
        font-size: 14px;
    }
    
    .value {
        font-weight: 500;
        color: #333;
        font-size: 14px;
        text-align: right;
    }
    
    .field-stats {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }
    
    .stat-icon {
        font-size: 24px;
    }
    
    .stat-value {
        display: block;
        font-weight: bold;
        font-size: 16px;
        color: #47866A;
    }
    
    .stat-label {
        display: block;
        font-size: 12px;
        color: #666;
    }
    
    .field-footer {
        padding: 15px 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;
    }
    
    .dates {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .date-label {
        font-size: 11px;
        color: #888;
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.completed {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-badge.planned {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-badge.problem {
        background: #f8d7da;
        color: #721c24;
    }
    
    .no-fields {
        text-align: center;
        padding: 50px 20px;
        grid-column: 1 / -1;
    }
    
    .no-fields p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .error-message {
        text-align: center;
        padding: 30px;
        color: #dc3545;
        grid-column: 1 / -1;
    }
    
    .my-fields-link {
        text-align: center;
        margin: 30px 0;
    }
    
    .btn-my-fields {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-my-fields:hover {
        background: #545b62;
        color: white;
        text-decoration: none;
    }
    
    .load-more-container {
        text-align: center;
        margin-top: 30px;
    }
    
    .load-more-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }
    
    .load-more-btn:hover {
        background: #545b62;
    }
    
    @media (max-width: 768px) {
        .journal-controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-controls {
            flex-direction: column;
        }
        
        .fields-container {
            grid-template-columns: 1fr;
        }
        
        .field-stats {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<script>
    function filterFields() {
        const cultureFilter = document.querySelector('.filter-select').value;
        const sortFilter = document.querySelectorAll('.filter-select')[1].value;
        
        console.log('Фильтр по культуре:', cultureFilter);
        console.log('Сортировка:', sortFilter);
    }
    
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', filterFields);
    });
    
    function loadMoreFields() {
        const btn = document.querySelector('.load-more-btn');
        const currentCount = document.querySelectorAll('.field-card').length;
        
        btn.disabled = true;
        btn.textContent = 'Загрузка...';
        
        fetch(`/api/fields/public?skip=${currentCount}`)
            .then(response => response.json())
            .then(data => {
                if (data.fields.length > 0) {
                    // Логика добавления новых полей
                } else {
                    document.querySelector('.load-more-container').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка загрузки данных');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Показать еще';
            });
    }
</script>
@endsection