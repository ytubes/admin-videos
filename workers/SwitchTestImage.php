<?php

namespace ytubes\admin\videos\workers;

use Yii;
use yii\db\Expression;
use yii\base\Model;

use ytubes\admin\videos\models\VideosStats;

/**
 * RecalculateCTR represents the model behind the search form about `ytubes\admin\videos\models\Videos`.
 */
class SwitchTestImage extends \yii\base\Object //implements Task\Handler\TaskHandlerInterface
{
    private $errors = [];

    /**
     * @var int default test item period (test shows);
     */
    const TEST_ITEM_PERIOD = 200;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function handle()
    {
        $this->switchTestImage();
    }

    /**
     * Меняет статус у тумб в категории, которые прошли тестирование.
     */
    public function switchTestImage()
    {
        $test_item_period = (int) Yii::$app->getModule('videos')->settings->get('test_item_period', self::TEST_ITEM_PERIOD);

            // Завершим тестовый период у тумб, если набралась необходимая статистика.
        $rows = VideosStats::find()
            ->select(['category_id', 'video_id', 'image_id'])
            ->where(['tested_image' => 0])
            ->andWhere(['>=', 'total_shows', $test_item_period])
            ->asArray()
            ->all();

        if (empty($rows)) {
            return;
        }

        $transaction = VideosStats::getDb()->beginTransaction();

        try {
            foreach ($rows as $row) {
                VideosStats::getDb()->createCommand()
                ->update(VideosStats::tableName(), ['tested_image' => 1], '`video_id`=:video_id AND `category_id`=:category_id AND `image_id`=:image_id')
                ->bindValue(':video_id', $row['video_id'])
                ->bindValue(':category_id', $row['category_id'])
                ->bindValue(':image_id', $row['image_id'])
                ->execute();
            }

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
        }

        /**
         * Для нескольких тумб: выбрать все видео. Затем проверить есть ли у текущего фото еще не закончившие тест.
         * Если все тумбы у видео закончили тест, то проверим, если ли у видео другие тумбы. Если есть, то начнем тестировать их.
         * Для этого снимем флажок "лучшая тумба" и переведем его на новую.
         * После того, как закончатся все тумбы (проверим, если нетестированные еще) Выберем лучшую тумбу из всех имеющихся по цтр
         * и выставим у нее флажок "лучшая тумба".
         */

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
