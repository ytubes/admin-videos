<?php
namespace ytubes\videos\admin\models\finders;

use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\base\Model;

use ytubes\videos\models\Video;
use ytubes\videos\models\RotationStats;
use ytubes\videos\models\Category;

/**
 * VideoFinder represents the model behind the search form about `ytubes\videos\admin\models\Video`.
 */
class VideoFinder extends Model
{
    public $slug;
    public $page;

    private $totalItems;

    private $sort;

    /**
     * @var int default items per page
     */
    const ITEMS_PER_PAGE = 20;
    /**
     * @var int default test items percentage (on page);
     */
    const TEST_ITEMS_PERCENT = 0;

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
		return Video::find();
	}
	public static function findById($id)
	{
		return self::find()
			->where(['video_id' => $id])
			->one();
	}
	public static function findBySourceUrl($source_url)
	{
		return self::find()
			->where(['source_url' => $source_url])
			->one();
	}
	public static function findByEmbedCode($embed_code)
	{
		return self::find()
			->where(['embed' => $embed_code])
			->one();
	}

    /**
     * Получает ролики постранично в разделе "все", отсортированные по дате.
     */
    public function getAllItems($params)
    {
        $videos = [];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            return $videos;
        }

        $videosSearch = Video::find()
            ->with(['categories' => function ($categoryQquery) {
                $categoryQquery->select(['category_id', 'title', 'slug', 'h1']);
            }])
            ->with('image')
            ->where(['status' => 10]);

        $this->totalItems = $videosSearch->count();

        $items_per_page = (int) Yii::$app->getModule('videos')->settings->get('items_per_page', self::ITEMS_PER_PAGE);
        $offset = ($this->page - 1 ) * $items_per_page;

        $videos = $videosSearch->orderBy(['published_at' => SORT_DESC])
            ->limit($items_per_page)
            ->offset($offset)
            ->all();

        return $videos;
    }
}
