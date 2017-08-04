<?php
namespace ytubes\videos\admin\models;

use Yii;
use ytubes\videos\admin\Module;

class Settings extends \yii\base\Model
{
	/**
	 * @var integer количество видео роликов на страницу (тумбы в категориях, или в новых, например)
	 */
	public $items_per_page;
	/**
	 * @var integer количество кнопок в строке пагинации (можно указать произвольно)
	 */
	public $pagination_buttons_count;
	public $recalculate_ctr_period;
	public $test_item_period;
	public $test_items_percent;
	public $test_items_start;
	/**
	 * @var boolean включить отображение виджета.
	 */
	public $related_enable;
	/**
	 * @var integer количество похожих видео.
	 */
	public $related_number;
	/**
	 * @var boolean учитывать или нет описание исходного видео при поиске "похожих" видео.
	 */
	public $related_allow_description;
	/**
	 * @var boolean учитывать или нет категории исходного видео при поиске "похожих" видео.
	 */
	public $related_allow_categories;

	private $module_id = 'videos';
	private $settings = 'settings';

	public function __construct($config = [])
	{
		parent::__construct($config = []);

		$this->settings = Module::getInstance()->settings;

		$this->module_id = 'videos';
		$this->attributes = $this->settings->getAll();
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items_per_page'], 'integer', 'integerOnly' => true, 'min' => 1],
            [['pagination_buttons_count', 'recalculate_ctr_period', 'test_item_period'], 'integer'],
            [['test_items_start'], 'integer', 'integerOnly' => true, 'min' => 3],
            [['test_items_percent'], 'integer', 'integerOnly' => true, 'min' => 0],
            ['test_items_percent', 'validateTestPercent'],
            ['items_per_page', 'minPerPageCheck'],
            [['related_number'], 'integer', 'integerOnly' => true, 'min' => 0],
            [['related_enable', 'related_allow_description', 'related_allow_categories'], 'boolean'],
        ];
    }

    public function validateTestPercent($attribute, $params, $validator)
    {
        $itemsPerPage = (int) $this->items_per_page;
        $testItemsStart = (int) $this->test_items_start;
		$potentialTestItemsOnPage = $itemsPerPage - $testItemsStart;

        $maxPercent = (int) floor((100 * $potentialTestItemsOnPage) / $itemsPerPage);

        if ($this->$attribute > 0 && $this->$attribute > $maxPercent) {
            $this->addError($attribute, 'Процент тестовых тумб на странице превышает допустимые пределы. Максимальный процент: ' . $maxPercent);
        }
    }

    public function minPerPageCheck($attribute, $params, $validator)
    {
        $itemsPerPage = (int) $this->items_per_page;
        $testItemsStart = (int) $this->test_items_start;
        $testItemsPercent = (int) $this->test_items_percent;

        if ($itemsPerPage < $testItemsStart && $testItemsPercent > 0) {
            $this->addError($attribute, 'Количество тумб на страницу должно быть равно или превышать стартовую тестовую позицию');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            /*'items_per_page' => 'Количество тумб на страницу',
            'pagination_buttons_count' => 'Количество кнопок в пагинации',
            'recalculate_ctr_period' => 'Период пересчета ЦТР',
            'test_item_period' => 'Тестовый период тумбы (в показах)',
            'test_items_percent' => 'Процент тестовых тумб на странице',
            'test_items_start' => 'После какой тумбы будет тестовая зона',
            'related_enable' => 'Включить отображение виджета "Похожие видео"',
            'related_number' => 'Сколько похожих роликов искать',
            'related_allow_description' => 'Учитывать описание',
            'related_allow_categories' => 'Учитывать категории',*/
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
