<?php
namespace ytubes\videos\admin\cron\jobs;

use Yii;

use ytubes\videos\models\RotationStats;
use ytubes\videos\models\Category;
use ytubes\videos\models\Image;
use ytubes\videos\models\finders\VideoFinder;
use ytubes\videos\models\finders\CategoryFinder;

/**
 * SetCategoriesThumbs устанавливает категорийные тумбы исходя из данных по цтр тумб к видео
 */
class SetCategoriesThumbs
{
    private $errors = [];

    public function handle()
    {
        $this->setCategoriesThumbs();
    }

    /**
     * Устанавливает главную тумбу категории
     */
    public function setCategoriesThumbs()
    {
        $categories = CategoryFinder::getActiveCategories();

        if (empty($categories)) {
            return;
        }

            //SELECT `image_id` FROM `videos_stats` WHERE (`category_id`=20) AND (`best_image`=1) AND `image_id` NOT IN (1,2,3) ORDER BY `ctr` LIMIT 1
        try {
            $usedImagesIds = [];

            $searchModel = new VideoFinder();

            foreach ($categories as $category) {

                    // Выбрать тумбы с первой страницы категории
                $items = $searchModel->getVideosFromCategory($category);

                $imagesIds = [];

                if (empty($items)) {
                    continue;
                }

                foreach ($items as $item) {
                    $imagesIds[] = $item['image']['image_id'];
                }

                    // Отсеять уже использованные в других категориях (уникальные должны быть)
                $unusedIds = array_diff($imagesIds, $usedImagesIds);

                    // Если уникальные иды остались, то выбрать первую и установить ее как обложку категории.
                if (!empty($unusedIds)) {
                    $firstId = array_shift($unusedIds);
                    $img = Image::findOne($firstId);

                    if ($img instanceof Image) {
                        Yii::$app->db->createCommand()
                        	->update(Category::tableName(), ['image' => $img->filepath], "`category_id`={$category['category_id']}")
                        	->execute();
                    }

                        // Записать, что данная тумба уже используется.
                    $usedImagesIds[] = $img->image_id;
                }
            }

        } catch(\Exception $e) {
            throw $e;
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
