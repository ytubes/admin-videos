<?php

namespace ytubes\admin\videos;

use \Yii;
use \yii\base\Object;

class Events extends Object
{
    public static function onSettingsMenuInit($event)
    {
        $event->sender->addItem([
            'label' => 'Видео',
            'group' => 'modules',
            'url' => ['/videos/settings'],
            'icon' => '<i class="fa fa-video-camera"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'videos' && Yii::$app->controller->id === 'settings')
        ]);
    }

}