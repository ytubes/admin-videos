<?php

namespace ytubes\admin\videos;

use Yii;

/**
 * videos module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'ytubes\admin\videos\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
        Yii::configure($this, require(__DIR__ . '/config.php'));
    }
}
