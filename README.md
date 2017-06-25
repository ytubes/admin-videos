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
        'class' => 'ytubes\admin\videos\Module',
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
\ytubes\admin\videos\workers\RecalculateCTR */2 * * * *
\ytubes\admin\videos\workers\SwitchTestImage */2 * * * *
\ytubes\admin\videos\workers\ShiftCheckpoint */2 * * * *
\ytubes\admin\videos\workers\SetCategoriesThumbs */5 * * * * (раз в пять минут)
```
