<?php

namespace ytubes\admin\videos\workers;

use Yii;
use yii\db\Expression;
use yii\base\Model;

use ytubes\admin\videos\models\VideosStats;

/**
 * RecalculateCTR represents the model behind the search form about `ytubes\admin\videos\models\Videos`.
 */
class ShiftCheckpoint extends \yii\base\Object //implements Task\Handler\TaskHandlerInterface
{
    private $errors = [];

    /**
     * @var int default recalculate ctr period (shows);
     */
    const RECALCULATE_CTR_PERIOD = 2000;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function handle()
    {
        $this->shiftCheckpoint();
    }

    /**
     * Метод смещает контрольные точки у тумб. Всего контрольных точек пять.
     * Значит берем клики периода рекалькуляции цтр и раскидываем равномерно по пяти точкам.
     * Затем выберем все тумбы, которые достигли необходимого значения, обнулим счетчик и сместим вправо на следующую точку.
     */
    public function shiftCheckpoint()
    {
        $recalculate_ctr_period = (int) Yii::$app->getModule('videos')->settings->get('recalculate_ctr_period', self::RECALCULATE_CTR_PERIOD);
        $showsCheckpointValue = (int) ceil($recalculate_ctr_period / 5);

        $thumbStats = VideosStats::find()
            ->where(['>=', 'current_shows', $showsCheckpointValue])
            ->asArray()
            ->all();

        if (empty($thumbStats)) {
            return;
        }

        $transaction = VideosStats::getDb()->beginTransaction();
        try {

            foreach ($thumbStats as $thumbStat) {
                $currentIndex = (int) $thumbStat['current_index'];

                if ($currentIndex == 4) {
                    $currentIndex = 0;
                } else {
                    $currentIndex ++;
                }

                   VideosStats::getDb()->createCommand()
                    ->update(VideosStats::tableName(), [
                        'current_shows' => 0,
                        'current_clicks' => 0,
                        'current_index' => $currentIndex,
                        "shows{$currentIndex}" => (int) $thumbStat['current_shows'],
                        "clicks{$currentIndex}" => (int) $thumbStat['current_clicks'],
                    ], '`video_id`=:video_id AND `category_id`=:category_id AND `image_id`=:image_id')
                    ->bindValue(':video_id', (int) $thumbStat['video_id'])
                    ->bindValue(':category_id', (int) $thumbStat['category_id'])
                    ->bindValue(':image_id', (int) $thumbStat['image_id'])
                       ->execute();
            }

            $transaction->commit();

        } catch(\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
        }
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
