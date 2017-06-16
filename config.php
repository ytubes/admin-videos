<?php

use backend\widgets\SettingsMenu;

return [
    'id' => 'videos',
    'class' => 'ytubes\admin\videos\Module',
    'namespace' => 'ytubes\admin\videos',
    'events' => [
        ['class' => SettingsMenu::className(),'event' => SettingsMenu::EVENT_INIT, 'callback' => ['ytubes\admin\videos\Events', 'onSettingsMenuInit']],
    ],
];