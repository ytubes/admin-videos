<?php

namespace ytubes\admin\videos\models;

use Yii;

/**
 * This is the model class for table "cron_tasks".
 *
 * @property integer $id
 * @property string $module
 * @property string $handler_class
 * @property string $cron_expression
 * @property integer $priority
 * @property integer $enabled
 * @property string $first_execution
 * @property string $last_execution
 * @property double $duration
 * @property integer $status
 */
class Settings extends \yii\base\Model
{
	private $module_id;

	public $items_per_page;
	public $pagination_buttons_count;
	public $recalculate_ctr_period;
	public $related_number;
	public $test_item_period;
	public $test_items_percent;
	public $test_items_start;

	protected $settings = 'settings';

	public function __construct($config = [])
	{
		parent::__construct($config = []);

		$this->settings = Yii::$app->getModule('videos')->settings;

		$this->module_id = 'videos';
		$this->attributes = $this->settings->getAll();
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items_per_page', 'pagination_buttons_count', 'recalculate_ctr_period', 'related_number', 'test_item_period'], 'integer'],
            [['test_items_percent', 'test_items_start'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'items_per_page' => 'Количество тумб на страницу',
            'pagination_buttons_count' => 'Количество кнопок в пагинации',
            'recalculate_ctr_period' => 'Период пересчета ЦТР',
            'related_number' => 'Количество похожих роликов',
            'test_item_period' => 'Тестовый период тумбы (в кликах)',
            'test_items_percent' => 'Процент тестовых тумб на странице',
            'test_items_start' => 'После какой тумбы будет тестовая зона',
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function save()
    {
        if ($this->validate()) {
        	$attributes = $this->toArray();

        	if (!empty($attributes)) {
        		foreach ($attributes as $name => $value) {
        			$this->settings->set($name, $value);
        		}
        	}

            return true;
        } else {
            return false;
        }
    }
}
