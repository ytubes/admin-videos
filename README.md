# admin-videos
Модуль видео для админки

# composer
"require": {
	"ytubes/admin-videos": "~0.1"
},

# Подключение модуля в админке:
В приложении backend backend/config/components.php прописать:
'modules' => [
    'videos' => [
        'class' => 'ytubes\admin\videos\Module',
    ],
],

# Для миграций
В консольном приложении: console/config/components.php прописать:

'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => [
        	'@vendor/ytubes/admin-videos/migrations',
        ],
    ],
],

# Крон
Также для нормальной работы нужные воркеры для крона:

\ytubes\admin\videos\workers\RecalculateCTR * * * * *
\ytubes\admin\videos\workers\SwitchTestImage * * * * *
\ytubes\admin\videos\workers\ShiftCheckpoint * * * * *
\ytubes\admin\videos\workers\SetCategoriesThumbs */5 * * * * (раз в пять минут)