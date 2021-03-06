<?php
namespace ytubes\videos\admin\models\finders;

use yii\base\Model;
use ytubes\videos\models\Category;

/**
 * CategoryFinder represents the model behind the search form about `ytubes\videos\admin\models\Category`.
 */
class CategoryFinder extends Model
{
	public $slug;
	public $page;

	private $totalItems;

	private $sort;

	protected $categoriesIndexedById;
	protected $categoriesIndexedBySlug;
	protected $categoriesIdsNamesArray;

	public function __construct($config = [])
	{
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
            /*[['video_id', 'image_id', 'user_id', 'orientation', 'duration', 'on_index', 'likes', 'dislikes', 'comments_num', 'views', 'status'], 'integer'],
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
		return Category::find();
	}

	public static function findById($id)
	{
		return self::find()
			->where(['category_id' => (int) $id])
			->one();
	}

	public static function findByTitle($title)
	{
		return self::find()
			->where(['title' => $title])
			->one();
	}

	public static function findBySlug($slug)
	{
		return self::find()
			->where(['slug' => $slug])
			->one();
	}

		// Нужна ли?
	/*public function getCategoryById($id)
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
	}*/

	public static function getDropDownList()
	{
		$categories = self::find()
			->select(['category_id', 'title'])
			->asArray()
			->all();

		if (!empty($categories)) {
			return array_column($categories, 'title', 'category_id');
		}

		return $categories;
	}
}
