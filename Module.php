<?php
namespace ytubes\videos\admin;

use Yii;

/**
 * videos module definition class
 */
class Module extends \ytubes\components\Module
{
    /**
     * @inheritdoc
     */
	public $name = 'Видео';
    /**
     * @inheritdoc
     */
	public $description = 'Модуль для админки видео';
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'ytubes\videos\admin\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'index/index';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
        //Yii::configure($this, require(__DIR__ . '/config.php'));
    }

    public function getName()
    {
    	return $this->name;
    }

    public function getDescription()
    {
    	return $this->description;
    }

    public function getId()
    {
    	return $this->id;
    }
}
