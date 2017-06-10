<?php

namespace backend\modules\videos;

use Yii;

/**
 * videos module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'backend\modules\videos\controllers';

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
