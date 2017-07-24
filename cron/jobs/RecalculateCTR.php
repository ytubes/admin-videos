<?php
namespace ytubes\videos\admin\cron\jobs;

use Yii;
use yii\db\Expression;

use ytubes\videos\admin\models\RotationStats;

/**
 * RecalculateCTR пересчитывает ЦТР видео
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
        RotationStats::getDb()->createCommand()
            ->update(RotationStats::tableName(), [
                'total_shows' => new Expression('current_shows+shows0+shows1+shows2+shows3+shows4'),
                'total_clicks' => new Expression('current_clicks+clicks0+clicks1+clicks2+clicks3+clicks4'),
            ])
            ->execute();

        RotationStats::getDb()->createCommand()
            ->update(RotationStats::tableName(), ['ctr' => new Expression('total_clicks / total_shows')])
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
