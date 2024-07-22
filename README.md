# OctoberCMS Backup Log

This plugin provides a backend log for backup your database into your storage.

## Before installing

make sure in root `composer.json` you added `extra` configuration like this:

```json
"extra": {
        "merge-plugin": {
            "include": [
                "plugins/*/*/composer.json"
            ],
            "recurse": true,
            "replace": false,
            "merge-dev": false
        }
    },
```

## Documentation

1. Run composer require. 

    ```
    composer require octobro/backuplog-plugin
    ```
2. After that, update the composer. It will added `spatie/laravel-backup` package.
    ```
    composer update
    ```
3. Run vendor publish to inject `backup.php` on your config folder.
    ```
    php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
    ```
4. Run october migrate.
    ```
    php artisan october:migrate
    ```

5. Add more database dumper setting on your `config/database.php` something like this. Skip if you already set some of variables.

    ```
    'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'database'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'dump' => [
                'dump_binary_path' => env('DATABASE_DUMP_BINARY_PATH', '/usr/bin'),
                'use_single_transaction',
                'excludeTables' => explode(',', env('DATABASE_DUMP_EXCLUDE_TABLES', 'system_event_logs,backend_access_logs'))
            ],
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : []
        ],
    ```
### Configuration

There's an env configuration in this plugin something likes:

```
BACKUP_LOG_TRIGGER_FUNCTION=dailyAt
BACKUP_LOG_TRIGGER_AT="23:59"
BACKUP_LOG_TRIGGER_IN_DAY=true
BACKUP_LOG_OVERRIDE_SCHEDULE=false

DATABASE_DUMP_BINARY_PATH="/usr/bin"
DATABASE_DUMP_EXCLUDE_TABLES="system_event_logs,backend_access_logs"
```

you can change that suitable for your machine.

### Schedule Work

There's has schedule worker to backup your database automatically in several period, In order to run that worker, please activate your `schedule:work` :

```
php artisan schedule:work
```

## Possible Future Features
If you want any of these features, please request it on Github or open a PR
 - Backup Source Code
 - Configuration Retention Backup
 - Notify Backup