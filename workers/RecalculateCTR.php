<?php

namespace ytubes\admin\videos\workers;

use Yii;
use yii\db\Expression;
use yii\base\Model;

use ytubes\admin\videos\models\VideosStats;

/**
 * RecalculateCTR represents the model behind the search form about `ytubes\admin\videos\models\Videos`.
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
