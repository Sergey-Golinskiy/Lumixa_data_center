# Configuration Guide - Lumixa Manufacturing System (LMS)

## Файл конфигурации

Основной конфиг находится в `config/config.php`.

## Параметры конфигурации

### Окружение приложения

| Параметр | Тип | Значения | Описание |
|----------|-----|----------|----------|
| `app_env` | string | `prod`, `dev` | Режим окружения |
| `app_debug` | bool | `true`, `false` | Режим отладки (показ ошибок) |
| `app_url` | string | URL | Базовый URL приложения |
| `app_name` | string | text | Название приложения |
| `app_version` | string | semver | Версия приложения |

### База данных

| Параметр | Тип | Описание |
|----------|-----|----------|
| `db_host` | string | Хост MySQL сервера |
| `db_port` | int | Порт (по умолчанию 3306) |
| `db_name` | string | Имя базы данных |
| `db_user` | string | Пользователь MySQL |
| `db_pass` | string | Пароль MySQL |
| `db_charset` | string | Кодировка (utf8mb4) |

### Сессии

| Параметр | Тип | Описание |
|----------|-----|----------|
| `session_lifetime` | int | Время жизни сессии в секундах |
| `session_name` | string | Имя cookie сессии |

### Безопасность

| Параметр | Тип | Описание |
|----------|-----|----------|
| `csrf_enabled` | bool | Включить CSRF защиту |
| `login_max_attempts` | int | Макс. попыток входа |
| `login_lockout_time` | int | Время блокировки (сек) |

### Файлы

| Параметр | Тип | Описание |
|----------|-----|----------|
| `upload_max_size` | int | Макс. размер загрузки (байт) |
| `upload_allowed_types` | array | Разрешенные типы файлов |

### Логирование

| Параметр | Тип | Описание |
|----------|-----|----------|
| `log_level` | string | Минимальный уровень (DEBUG, INFO, WARNING, ERROR) |
| `log_file` | string | Путь к файлу лога |

### Бэкапы

| Параметр | Тип | Описание |
|----------|-----|----------|
| `backup_path` | string | Путь к директории бэкапов |
| `backup_max_count` | int | Максимум хранимых бэкапов |

### Бизнес настройки

| Параметр | Тип | Описание |
|----------|-----|----------|
| `currency` | string | Код валюты (UAH) |
| `currency_symbol` | string | Символ валюты (₴) |
| `overhead_percent` | float | Процент накладных расходов |
| `costing_method` | string | Метод расчета стоимости (AVG) |

## Пример конфигурации

```php
<?php
// config/config.php

return [
    // === Application ===
    'app_env' => 'prod',
    'app_debug' => false,
    'app_url' => 'https://lms.lumixa.io',
    'app_name' => 'Lumixa LMS',
    'app_version' => '1.0.0',

    // === Database ===
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_name' => 'lms_db',
    'db_user' => 'lms_user',
    'db_pass' => 'secure_password_here',
    'db_charset' => 'utf8mb4',

    // === Session ===
    'session_lifetime' => 86400, // 24 hours
    'session_name' => 'lms_session',

    // === Security ===
    'csrf_enabled' => true,
    'login_max_attempts' => 5,
    'login_lockout_time' => 900, // 15 minutes

    // === Files ===
    'upload_max_size' => 10485760, // 10MB
    'upload_allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],

    // === Logging ===
    'log_level' => 'WARNING', // DEBUG in dev
    'log_file' => __DIR__ . '/../storage/logs/app.log',

    // === Backup ===
    'backup_path' => __DIR__ . '/../storage/backups',
    'backup_max_count' => 10,

    // === Timezone ===
    'timezone' => 'Europe/Kiev',

    // === Business ===
    'currency' => 'UAH',
    'currency_symbol' => '₴',
    'overhead_percent' => 15.0,
    'costing_method' => 'AVG',

    // === Pagination ===
    'items_per_page' => 25,
    'max_items_per_page' => 100,
];
```

## Переопределение для разных окружений

### Разработка (dev)

Создайте `config/config.dev.php`:

```php
<?php
// config/config.dev.php
$config = require __DIR__ . '/config.php';

return array_merge($config, [
    'app_env' => 'dev',
    'app_debug' => true,
    'log_level' => 'DEBUG',
]);
```

### Тестирование

```php
<?php
// config/config.test.php
$config = require __DIR__ . '/config.php';

return array_merge($config, [
    'app_env' => 'test',
    'db_name' => 'lms_test',
]);
```

## Важные замечания

### Безопасность паролей

**НИКОГДА** не коммитьте реальные пароли в git!

Рекомендации:
- Используйте `config/config.php.example` как шаблон
- Добавьте `config/config.php` в `.gitignore`
- На сервере создайте `config/config.php` вручную

### APP_DEBUG в production

**ВСЕГДА** устанавливайте `app_debug => false` на production!

Debug режим:
- Показывает детальные ошибки
- Раскрывает внутреннюю структуру приложения
- Потенциально утечка sensitive данных

### Права на config

```bash
# Ограничьте доступ к config файлам
chmod 640 /var/www/lms/config/config.php
chown www-data:www-data /var/www/lms/config/config.php
```

## Проверка конфигурации

После изменения конфигурации:

1. Откройте `/admin/diagnostics`
2. Проверьте статус всех компонентов
3. При ошибках - следуйте рекомендациям

## Environment Variables (альтернатива)

Если сервер поддерживает env переменные:

```bash
# .env file (не коммитить!)
APP_ENV=prod
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=lms_db
DB_USER=lms_user
DB_PASS=secure_password
```

```php
// В config.php можно читать:
'db_host' => getenv('DB_HOST') ?: 'localhost',
```
