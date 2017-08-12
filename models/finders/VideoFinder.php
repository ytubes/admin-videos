<?php
namespace ytubes\videos\admin\models\finders;

use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\base\Model;
use yii\helpers\StringHelper;

use ytubes\videos\models\Video;
use ytubes\videos\models\RotationStats;
use ytubes\videos\models\Category;

/**
 * VideoFinder represents the model behind the search form about `ytubes\videos\admin\models\Video`.
 */
class VideoFinder extends Model
{
    public $per_page = 50;
    public $videos_ids = '';
    public $categories_ids = [];
    public $user_id;
    public $status;
    public $title;

    public $show_thumb = false;

    public $bulk_edit = false;

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
            [['user_id', 'status', 'per_page'], 'integer'],
            [['show_thumb', 'bulk_edit'], 'boolean'],

            ['videos_ids', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                return StringHelper::explode($value, $delimiter = ',', true, true);
            }],
            ['videos_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true],
            ['videos_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

            ['categories_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            ['categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

            [['title'], 'string'],
            ['title', 'filter', 'filter' => 'trim', 'skipOnEmpty' => true],
        ];
    }

    /**
     * Получает ролики постранично в разделе "все", отсортированные по дате.
     */
    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->per_page,
            ],
            'sort'=> [
                'defaultOrder' => [
                    'published_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('1=0');

            return $dataProvider;
        }

        $dataProvider->pagination->pageSize = $this->per_page;

        if ($this->title) {
            $query
                ->select(['*', 'MATCH (`title`, `description`, `short_description`) AGAINST (:query) AS `relevance`'])
                ->where('MATCH (`title`, `description`, `short_description`) AGAINST (:query IN BOOLEAN MODE)', [
                    ':query'=> $this->title,
                ])
                ->orderBy(['relevance' => SORT_DESC]);
        }

        $query->andFilterWhere([
            'video_id' => $this->videos_ids,
            'user_id' => $this->user_id,
            'status' => $this->status,
        ]);

        return $dataProvider;
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
}
