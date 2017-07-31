<?php

return [
    'id' => 'videos',
    'class' => 'ytubes\videos\admin\Module',
    'namespace' => 'ytubes\videos\admin',
    'events' => [
        [
            'class' => 'backend\widgets\SettingsMenu',//::className(),
            'event' => backend\widgets\SettingsMenu::EVENT_INIT,
            'callback' => ['ytubes\videos\admin\Events', 'onSettingsMenuInit'],
        ],
    ],
];
