# Deployment Guide - Lumixa Manufacturing System (LMS)

## Требования к серверу

### Минимальные требования
- **PHP**: 8.2 или выше
- **MySQL**: 8.0 или выше (или MariaDB 10.6+)
- **Web Server**: Nginx или Apache с mod_rewrite
- **Disk Space**: минимум 500MB для приложения + место для данных
- **RAM**: минимум 512MB (рекомендуется 1GB+)

### PHP Extensions (обязательные)
```
- pdo_mysql    # MySQL драйвер
- mbstring    # Многобайтовые строки
- openssl     # Шифрование
- json        # JSON обработка
- zip         # Архивы для backup
```

### PHP Extensions (рекомендуемые)
```
- curl        # HTTP клиент
- gd          # Обработка изображений
- intl        # Интернационализация
```

## Шаги установки

### 1. Загрузка файлов

Загрузите файлы проекта на сервер:

```bash
# Пример через git
cd /var/www
git clone https://github.com/your-repo/lumixa-lms.git lms
cd lms

# Или через SCP/SFTP
scp -r ./lumixa-lms user@server:/var/www/lms
```

### 2. Настройка прав доступа

```bash
# Владелец - web server user (обычно www-data)
sudo chown -R www-data:www-data /var/www/lms

# Права на директории
sudo chmod -R 755 /var/www/lms

# Права на запись для storage
sudo chmod -R 775 /var/www/lms/storage
sudo chmod -R 775 /var/www/lms/public/uploads

# Создание необходимых директорий (если не существуют)
mkdir -p /var/www/lms/storage/logs
mkdir -p /var/www/lms/storage/backups
mkdir -p /var/www/lms/public/uploads
```

### 3. Конфигурация Web Server

#### Nginx

```nginx
server {
    listen 80;
    server_name lms.lumixa.io;
    root /var/www/lms/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Запрет доступа к приватным директориям
    location ~ ^/(app|config|storage|docs|views)/ {
        deny all;
        return 404;
    }

    # Запрет доступа к скрытым файлам
    location ~ /\. {
        deny all;
        return 404;
    }

    # Статические файлы
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # PHP обработка
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
}
```

#### Apache (.htaccess уже включен в public/)

Убедитесь, что mod_rewrite включен:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Конфигурация виртуального хоста:

```apache
<VirtualHost *:80>
    ServerName lms.lumixa.io
    DocumentRoot /var/www/lms/public

    <Directory /var/www/lms/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Запрет доступа к приватным директориям
    <DirectoryMatch "^/var/www/lms/(app|config|storage|docs|views)">
        Require all denied
    </DirectoryMatch>

    ErrorLog ${APACHE_LOG_DIR}/lms_error.log
    CustomLog ${APACHE_LOG_DIR}/lms_access.log combined
</VirtualHost>
```

### 4. Создание базы данных MySQL

```sql
-- Подключитесь к MySQL
mysql -u root -p

-- Создайте базу данных
CREATE DATABASE lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создайте пользователя
CREATE USER 'lms_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- Выдайте права
GRANT ALL PRIVILEGES ON lms_db.* TO 'lms_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Конфигурация приложения

Отредактируйте `config/config.php`:

```php
<?php
return [
    // Окружение
    'app_env' => 'prod',          // prod или dev
    'app_debug' => false,         // true только для отладки!
    'app_url' => 'https://lms.lumixa.io',
    'app_name' => 'Lumixa LMS',

    // База данных
    'db_host' => 'localhost',
    'db_name' => 'lms_db',
    'db_user' => 'lms_user',
    'db_pass' => 'your_secure_password',

    // Timezone
    'timezone' => 'Europe/Kiev',

    // Session
    'session_lifetime' => 86400,  // 24 часа
];
```

### 6. Запуск Setup Wizard

1. Откройте в браузере: `https://lms.lumixa.io/setup`
2. Система проверит окружение
3. При успешных проверках:
   - Введите данные администратора
   - Нажмите "Install"
4. После установки вы будете перенаправлены на страницу входа

### 7. Первый вход

- URL: `https://lms.lumixa.io/login`
- Логин: email, указанный при установке
- Пароль: пароль, указанный при установке
- При первом входе система может попросить сменить пароль

## Проверка установки

### Health Check

```bash
curl -I https://lms.lumixa.io/health
# Должен вернуть: HTTP/1.1 200 OK
```

### Diagnostics

После входа как Admin:
1. Перейдите в Admin → Diagnostics
2. Убедитесь, что все проверки пройдены (✅)
3. При наличии ❌ следуйте рекомендациям

## Обновление

### 1. Backup перед обновлением

```bash
# Через UI: Admin → Backups → Create Backup
# Или вручную:
mysqldump -u lms_user -p lms_db > backup_$(date +%Y%m%d).sql
tar -czf uploads_$(date +%Y%m%d).tar.gz /var/www/lms/public/uploads
```

### 2. Загрузка новых файлов

```bash
cd /var/www/lms
git pull origin main
# Или через SFTP
```

### 3. Применение миграций

Через UI: Admin → Diagnostics → Run Pending Migrations

Или через CLI (если доступен):
```bash
php /var/www/lms/cli/migrate.php
```

### 4. Очистка кэша (если используется)

```bash
rm -rf /var/www/lms/storage/cache/*
```

## HTTPS (рекомендуется)

Используйте Let's Encrypt для бесплатного SSL:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d lms.lumixa.io
```

## Мониторинг

### Логи приложения
```bash
tail -f /var/www/lms/storage/logs/app.log
```

### Логи web server
```bash
# Nginx
tail -f /var/log/nginx/error.log

# Apache
tail -f /var/log/apache2/lms_error.log
```

### Health endpoint для мониторинга
```bash
# Добавьте в cron или monitoring tool:
curl -sf https://lms.lumixa.io/health || echo "LMS is down!"
```

## Troubleshooting

См. документ [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) для решения типичных проблем.
