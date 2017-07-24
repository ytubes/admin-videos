<?php
namespace ytubes\videos\admin\models\forms;

use Yii;
use SplFileObject;

use yii\base\InvalidParamException;

use yii\db\Expression;

use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;

use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

use ytubes\videos\admin\models\Video;
use ytubes\videos\admin\models\Category;
use ytubes\videos\admin\models\Image;
use ytubes\videos\admin\models\RotationStats;
use ytubes\videos\admin\models\VideosRelatedMap;

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class Tools extends \yii\base\Model
{
	public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_rows;
    public $csv_file;

    public $replace;

    protected $model;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delimiter', 'fields'], 'required'],
            ['fields', 'each', 'rule' => ['string'], 'skipOnEmpty' => false],
            [['delimiter', 'enclosure', 'csv_rows'], 'filter', 'filter' => 'trim'],
            [['delimiter', 'enclosure', 'csv_rows'], 'string'],
            [['replace'], 'boolean'],
            ['replace', 'default', 'value' => false],

            [['csv_file'], 'file', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => true, 'extensions' => 'csv', 'maxFiles' => 1, 'mimeTypes' => 'text/plain'],
        ];
    }

    /**
     * Очищает стату по видео полностью.
     * @return boolean
     */
	public function clearStats()
	{
			// Очистка статистики тумб
		$q1 = Video::getDb()->createCommand()
			->update(RotationStats::tableName(), [
					'tested_image' => 0,
					'current_index' => 0,
					'current_shows' => 0,
					'current_clicks' => 0,
					'shows0' => 0,
					'clicks0' => 0,
					'shows1' => 0,
					'clicks1' => 0,
					'shows2' => 0,
					'clicks2' => 0,
					'shows3' => 0,
					'clicks3' => 0,
					'shows4' => 0,
					'clicks4' => 0,
					'total_shows' => 0,
					'total_clicks' => 0,
					'ctr' => 0,
				])
			->execute();

			// Очитска просмотров, лайков, дизлайков.
		$q2 = Video::getDb()->createCommand()
			->update(Video::tableName(), [
					'likes' => 0,
					'dislikes' => 0,
					'views' => 0,
				])
			->execute();

			// Очитска просмотров, лайков, дизлайков.
		$q3 = Video::getDb()->createCommand()
			->update(Category::tableName(), [
					'shows' => 0,
					'clicks' => 0,
					'ctr' => 0,
				])
			->execute();

		$result = $q1 + $q2 + $q3;

		return $result;
	}

    /**
     * Задает случайную дату в промежутке от текущего времени до года назад
     * @return boolean
     */
	public function randomDate()
	{
			// Рандом для видео в таблице `videos`
		$q1 = Video::getDb()->createCommand()
			->update(Video::tableName(), [
					'published_at' => new Expression('FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) - FLOOR(0 + (RAND() * 31536000)))'),
				])
			->execute();

			// Рандом в таблице videos_stats
		$sql = 'UPDATE `' . RotationStats::tableName() . '` as `vs`
				LEFT JOIN (
					SELECT `video_id`, `published_at` FROM `' . Video::tableName() . '`
				) as `v`
				ON `v`.`video_id`=`vs`.`video_id`
			SET `vs`.`published_at`=`v`.`published_at`';

		$q2 = Video::getDb()->createCommand($sql)
			->execute();

		$result = $q1 + $q2;

		return $result;
	}

    /**
     * Удаляет видео, тумбы, стату по ним. // Сделать через транзакции, добавить исключения
     * @return boolean
     */
	public function clearVideos()
	{
			// Очищаем стату тумб
		Video::getDb()->createCommand()
			->truncateTable(RotationStats::tableName())
			->execute();

			// Удаляем фотки
		Video::getDb()->createCommand()
			->delete(Image::tableName(), '1=1')
			->execute();

			// Удаляем видео
		Video::getDb()->createCommand()
			->delete(Video::tableName(), '1=1')
			->execute();

			// Очищаем релатеды.
		Video::getDb()->createCommand()
			->truncateTable(VideosRelatedMap::tableName())
			->execute();

			// Сброс автоинкремента у видео
		$sql = 'ALTER TABLE `' . Video::tableName() . '` AUTO_INCREMENT=1';
		Video::getDb()->createCommand($sql)
			->execute();

			// Сброс автоинкремента у скриншотов
		$sql = 'ALTER TABLE `' . Image::tableName() . '` AUTO_INCREMENT=1';
		Video::getDb()->createCommand($sql)
			->execute();

		return true;
	}
}
