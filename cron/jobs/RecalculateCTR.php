<?php
namespace ytubes\videos\admin\cron\jobs;

use Yii;
use yii\db\Expression;
use ytubes\videos\models\RotationStats;

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

/*
UPDATE `videos_stats`
SET `total_clicks` = `current_clicks` + `clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`,
    `total_shows` = `current_shows` + `shows0` + `shows1` + `shows2` + `shows3` + `shows4`

ALTER TABLE `videos_stats` ADD COLUMN `total_clicks` INTEGER UNSIGNED AS (`current_clicks` + `clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`);
ALTER TABLE `videos_stats` ADD COLUMN `total_shows` INTEGER UNSIGNED AS (`current_shows` + `shows0` + `shows1` + `shows2` + `shows3` + `shows4`);
ALTER TABLE `videos_stats` ADD COLUMN `ctr` DOUBLE AS (`current_clicks` / `current_shows`);
*/
    public function handle()
    {
        $this->recalculateCtr();
    }

    protected function recalculateCtr()
    {
            // Обновим total_shows и total_clicks на актуальный.
        RotationStats::getDb()->createCommand()
            ->update(RotationStats::tableName(), [
                'total_clicks' => new Expression('`current_clicks` + `clicks0` + `clicks1` + `clicks2` + `clicks3` + `clicks4`'),
                'total_shows' => new Expression('`current_shows` + `shows0` + `shows1` + `shows2` + `shows3` + `shows4`'),
                'ctr' => new Expression('`total_clicks` / `total_shows`'),
            ])
            ->execute();

        /*RotationStats::getDb()->createCommand()
            ->update(RotationStats::tableName(), ['ctr' => new Expression('total_clicks / total_shows')])
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
