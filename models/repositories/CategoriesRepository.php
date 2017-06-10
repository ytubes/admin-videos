<?php

namespace backend\modules\videos\models\repositories;

use Yii;

use yii\data\ActiveDataProvider;
use yii\data\Sort;

use backend\modules\videos\models\Videos;
use backend\modules\videos\models\VideosStats;
use backend\modules\videos\models\VideosCategories;

/**
 * VideosRepository represents the model behind the search form about `frontend\models\videos\Videos`.
 */
class CategoriesRepository extends \yii\base\Model
{
	public $slug;
	public $page;

	private $totalItems;

	private $sort;
	protected $params;

	protected $categoriesIndexedById;
	protected $categoriesIndexedBySlug;
	protected $categoriesIdsNamesArray;

	public function __construct($config = [])
	{
		$this->params = Yii::$app->params['videos'];
		parent::__construct($config);
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        	[['slug'], 'string'],
        	['page', 'integer', 'min' => 1],
			['page', 'default', 'value' => 1],
            /*[['video_id', 'image_id', 'user_id', 'orientation', 'duration', 'on_index', 'likes', 'dislikes', 'comments_count', 'views', 'status'], 'integer'],
            [['slug', 'title', 'description', 'short_description', 'video_url', 'embed', 'published_at', 'created_at', 'updated_at'], 'safe'],*/
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

	public static function find()
	{
		return VideosCategories::find();
	}

	public function getCategoryById($id)
	{
		$this->getCategoriesIndexedById();

		if (isset($this->categoriesIndexedById[$id])) {
			return $this->categoriesIndexedById[$id];
		}

		return null;
	}

	public function getCategoriesIndexedById()
	{
		if (null === $this->categoriesIndexedById) {
			$this->categoriesIndexedById = self::find()
				->indexBy('category_id')
				->all();
		}

		return $this->categoriesIndexedById;
	}

	public function getCategoryBySlug($slug)
	{
		$this->getCategoriesIndexedBySlug();

		if (isset($this->categoriesIndexedBySlug[$slug])) {
			return $this->categoriesIndexedBySlug[$slug];
		}

		return null;
	}

	public function getCategoriesIndexedBySlug()
	{
		if (null === $this->categoriesIndexedBySlug) {
			$this->categoriesIndexedBySlug = self::find()
				->indexBy('slug')
				->all();
		}

		return $this->categoriesIndexedBySlug;
	}

	public function getCategoryNameById($id)
	{
		$this->getCategoriesIdsNamesArray();

		if (isset($this->categoriesIdsNamesArray[$id])) {
			return $this->categoriesIdsNamesArray[$id];
		}

		return null;
	}

	public function getCategoriesIdsNamesArray()
	{
		if (null === $this->categoriesIdsNamesArray) {
			$categories = self::find()
				->select(['category_id', 'title'])
				->indexBy('category_id')
				->asArray()
				->all();

			if (!empty($categories)) {
				$this->categoriesIdsNamesArray = array_column($categories, 'title', 'category_id');
			} else {
				$this->categoriesIdsNamesArray = [];
			}
		}

		return $this->categoriesIdsNamesArray;
	}
}
