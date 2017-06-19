<?php

namespace ytubes\admin\videos\models\forms;

use Yii;
use SplFileObject;

use yii\base\InvalidParamException;

use yii\db\Expression;

use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;

use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

use ytubes\admin\videos\models\Videos;
use ytubes\admin\videos\models\VideosCategories;
use ytubes\admin\videos\models\VideosImages;
use ytubes\admin\videos\models\VideosStats;
use ytubes\admin\videos\models\VideosRelatedMap;

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
		$q1 = Videos::getDb()->createCommand()
			->update(VideosStats::tableName(), [
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
		$q2 = Videos::getDb()->createCommand()
			->update(Videos::tableName(), [
					'likes' => 0,
					'dislikes' => 0,
					'views' => 0,
				])
			->execute();

			// Очитска просмотров, лайков, дизлайков.
		$q3 = Videos::getDb()->createCommand()
			->update(VideosCategories::tableName(), [
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
		$q1 = Videos::getDb()->createCommand()
			->update(Videos::tableName(), [
					'published_at' => new Expression('FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) - FLOOR(0 + (RAND() * 31536000)))'),
				])
			->execute();

			// Рандом в таблице videos_stats
		$sql = 'UPDATE `' . VideosStats::tableName() . '` as `vs`
				LEFT JOIN (
					SELECT `video_id`, `published_at` FROM `' . Videos::tableName() . '`
				) as `v`
				ON `v`.`video_id`=`vs`.`video_id`
			SET `vs`.`published_at`=`v`.`published_at`';

		$q2 = Videos::getDb()->createCommand($sql)
			->execute();

		$result = $q1 + $q2;

		return $result;
	}

    /**
     * Удаляет видео, тумбы, стату по ним.
     * @return boolean
     */
	public function clearVideos()
	{
			// Очищаем стату тумб
		Videos::getDb()->createCommand()
			->truncateTable(VideosStats::tableName())
			->execute();

			// Удаляем фотки
		Videos::getDb()->createCommand()
			->delete(VideosImages::tableName(), '1=1')
			->execute();

			// Удаляем видео
		Videos::getDb()->createCommand()
			->delete(Videos::tableName(), '1=1')
			->execute();

			// Очищаем релатеды.
		Videos::getDb()->createCommand()
			->truncateTable(VideosRelatedMap::tableName())
			->execute();

			// Сброс автоинкремента у видео
		$sql = 'ALTER TABLE `' . Videos::tableName() . '` AUTO_INCREMENT=1';
		Videos::getDb()->createCommand($sql)
			->execute();

			// Сброс автоинкремента у скриншотов
		$sql = 'ALTER TABLE `' . VideosImages::tableName() . '` AUTO_INCREMENT=1';
		Videos::getDb()->createCommand($sql)
			->execute();

		return true;
	}


	/**
	 * Осуществляет вставку категории. Если таковая уже существует (чек по тайтлу и иду) то проверяется флажок, перезаписывать или нет.
	 * В случае перезаписи назначает новые параметры исходя из данных файла.
	 * @return boolean было ли произведено обновление или вставка
	 */
	protected function insertCategory($newCategory)
	{
			// Ищем, существует ли категория.
		if (isset($newCategory['category_id'])) {
			$category = VideosCategories::find()
				->where(['category_id' => $newCategory['category_id']])
				->one();
		} elseif (isset($newCategory['title'])) {
			$category = VideosCategories::find()
				->where(['title' => $newCategory['title']])
				->one();
		} else {
			throw new InvalidParamException();
		}

			// Если ничего не нашлось, будем вставлять новый.
		if (!($category instanceof VideosCategories)) {
			$category = new VideosCategories();
		} else {
				// Если переписывать не нужно существующую категорию, то просто проигнорировать ее.
			if ($this->replace == false) {
				return true;
			}
		}

		$category->attributes = $newCategory;

		if (!isset($newCategory['slug']) || empty($newCategory['slug'])) {
			$category->slug = \URLify::filter($newCategory['title']);
		}

		if ($category->isNewRecord) {
			$category->updated_at = gmdate('Y:m:d H:i:s');
			$category->created_at = gmdate('Y:m:d H:i:s');
		} else {
			$category->updated_at = gmdate('Y:m:d H:i:s');
		}

		return $category->save(true);
	}
}
