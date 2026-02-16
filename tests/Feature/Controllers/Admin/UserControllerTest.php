<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;

/**
 * Тесты для контроллера управления пользователями
 * Адаптированы для работы с кастомной системой аутентификации на PHP сессиях
 */
class UserControllerTest extends TestCase
{
    /**
     * Тест: Параметры фильтрации корректно передаются в URL
     * Проверяет что система принимает параметры поиска и фильтрации
     */
    public function test_filter_parameters_are_accepted_by_system()
    {
        // Тестируем различные комбинации параметров
        $testCases = [
            '/admin/users?search=test',
            '/admin/users?role=1&status=active',
            '/admin/users?search=john&role=2',
            '/admin/users?status=inactive',
        ];
        
        foreach ($testCases as $url) {
            try {
                $response = $this->get($url);
                
                // Проверяем что запрос не вызывает ошибок сервера (5xx)
                $this->assertLessThan(
                    500, 
                    $response->status(),
                    "Запрос $url вызвал ошибку сервера. Статус: " . $response->status()
                );
            } catch (\Exception $e) {
                $this->fail("Запрос $url вызвал исключение: " . $e->getMessage());
            }
        }
    }
    
}