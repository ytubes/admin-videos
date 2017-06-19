<?php

namespace ytubes\admin\videos\workers;

use Yii;
use \yii\db\Expression;

use ytubes\admin\videos\models\VideosStats;
use ytubes\admin\videos\models\VideosCategories;
use ytubes\admin\videos\models\VideosImages;
use ytubes\admin\videos\models\repositories\VideosRepository;

/**
 * RecalculateCTR represents the model behind the search form about `frontend\models\videos\Videos`.
 */
class SetCategoriesThumbs extends \yii\base\Object //implements Task\Handler\TaskHandlerInterface
{
    private $errors = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function handle()
    {
        $this->setCategoriesThumbs();
    }

    /**
     * Устанавливает главную тумбу категории
     */
    public function setCategoriesThumbs()
    {
        $categories = VideosCategories::find()
            ->all();

        if (empty($categories)) {
            return;
        }

            //SELECT `image_id` FROM `videos_stats` WHERE (`category_id`=20) AND (`best_image`=1) AND `image_id` NOT IN (1,2,3) ORDER BY `ctr` LIMIT 1
        try {
            $usedImagesIds = [];

            $searchModel = new VideosRepository();

            foreach ($categories as $category) {

                    // Выбрать тумбы с первой страницы категории
                $items = $searchModel->getItemsFromCategory([$searchModel->formName() => ['slug' => $category->slug, 'sort' => 'popular']], $category);

                $imagesIds = [];

                if (empty($items)) {
                    continue;
                }

                foreach ($items as $item) {
                    $imagesIds[] = $item->image->image_id;
                }

                    // Отсеять уже использованные в других категориях (уникальные должны быть)
                $unusedIds = array_diff($imagesIds, $usedImagesIds);

                    // Если уникальные иды остались, то выбрать первую и установить ее как обложку категории.
                if (!empty($unusedIds)) {
                    $firstId = array_shift($unusedIds);
                    $img = VideosImages::findOne($firstId);

                    if (null !== $img) {
                        $category->image = $img->filepath;
                        $category->save(true);
                    }

                        // Записать, что данная тумба уже используется.
                    $usedImagesIds[] = $img->image_id;
                }
            }

        } catch(\Exception $e) {
            //$transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            //$transaction->rollBack();
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
