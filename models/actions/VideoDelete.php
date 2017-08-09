<?php
namespace ytubes\videos\admin\models\actions;

use Yii;
use ytubes\videos\models\Video;
use ytubes\videos\models\Image;
use ytubes\videos\models\RotationStats;
use ytubes\videos\models\VideosRelatedMap;
use ytubes\videos\models\VideosCategoriesMap;

class VideoDelete
{
    private $video;
    private $is_deleted = false;

    public function __construct(Video $video)
    {
        $this->video = $video;
        $this->run();
    }

    private function run()
    {
        $video_id = $this->video->video_id;
            // Удалить данные по скриншотам (вместе с файлами)
        if ($this->video->hasImages()) {
            foreach ($this->video->images as $image) {
                $filepath = Yii::getAlias('@screenshots' . $image->filepath);
                $sourceFile = Yii::getAlias('@screenshots' . $image->source_url);

                if (is_file($filepath)) {
                    @unlink($filepath);
                    @unlink($sourceFile);
                }

                $image->delete();
            }
        }

            // Удалить данные по ротации
        Yii::$app->db->createCommand()
            ->delete(RotationStats::tableName(), "`video_id`={$video_id}")
            ->execute();

            // Удалить данные по категориям (еще не реализовано);
        if ($this->video->hasCategories()) {
            Yii::$app->db->createCommand()
                ->delete(VideosCategoriesMap::tableName(), "`video_id`={$video_id}")
                ->execute();
        }

            // Удалить данные по "похожим"
        Yii::$app->db->createCommand()
            ->delete(VideosRelatedMap::tableName(), "`video_id`={$video_id}")
            ->execute();

        if ($this->video->delete()) {
            $this->is_deleted = true;
        }
    }

    public function isDeleted()
    {
        return $this->is_deleted;
    }
}
