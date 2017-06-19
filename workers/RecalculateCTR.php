<?php

namespace ytubes\admin\videos\workers;

use Yii;
use yii\db\Expression;
use yii\base\Model;

use ytubes\admin\videos\models\VideosStats;

/**
 * RecalculateCTR represents the model behind the search form about `frontend\models\videos\Videos`.
 */
class RecalculateCTR extends \yii\base\Object //implements Task\Handler\TaskHandlerInterface
{
	protected $errors = [];

	public function __construct($config = [])
	{
		parent::__construct($config);
	}

	public function handle()
	{
		$this->recalculateCtr();
    }

	protected function recalculateCtr()
	{
    		// Обновим total_shows и total_clicks на актуальный.
   		VideosStats::getDb()->createCommand()
    		->update(VideosStats::tableName(), [
    				'total_shows' => new Expression('current_shows+shows0+shows1+shows2+shows3+shows4'),
    				'total_clicks' => new Expression('current_clicks+clicks0+clicks1+clicks2+clicks3+clicks4'),
    			])
   			->execute();

   		VideosStats::getDb()->createCommand()
    		->update(VideosStats::tableName(), ['ctr' => new Expression('total_clicks / total_shows')])
   			->execute();

			// Пересчитаем цтр уже оттестированных тумб
   		/*Yii::$app->db->createCommand()
   			//->createCommand('UPDATE `videos_stats` SET `ctr`=`clicks`/`shows` WHERE `tested_image`=1 AND `views`>=2000')
    		->update(VideosStats::tableName(), ['ctr' => new Expression('total_clicks / total_shows')], '`tested_image`=:tested_image AND `current_shows`>=:current_shows')
    		->bindValue(':tested_image', 1)
    		->bindValue(':current_shows', (int) $this->params['recalculate_ctr_period'] / 5)
   			->execute();*/
	}

    /**
     * {@inheritdoc}
     */
	public function getErrors()
	{
		return $this->errors;
	}

    /**
     * {@inheritdoc}
     */
	public function hasErrors()
	{
		return (!empty($this->errors));
	}
}
