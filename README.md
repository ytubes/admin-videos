# Admin videos
Модуль видео для админки

## Composer
```json
"require": {
    "ytubes/videos-admin": "~0.1"
},
```

## Подключение модуля в админке:
В приложении backend backend/config/components.php прописать:
```php
'modules' => [
    'videos' => [
        'class' => 'ytubes\videos\admin\Module',
    ],
],
```

## Для миграций
В консольном приложении: console/config/components.php прописать:
```php
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
           'migrationPath' => [
                '@vendor/ytubes/videos-admin/migrations',
        ],
    ],
],
```

## Крон
Также для нормальной работы нужные воркеры для крона:
```
\ytubes\videos\admin\cron\jobs\RecalculateCTR */2 * * * *
\ytubes\videos\admin\cron\jobs\SwitchTestImage */2 * * * *
\ytubes\videos\admin\cron\jobs\ShiftCheckpoint */2 * * * *
\ytubes\videos\admin\cron\jobs\SetCategoriesThumbs */5 * * * * (раз в пять минут)
```
