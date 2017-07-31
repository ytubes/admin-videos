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

    /**
     * Получает ролики для категории.
     */
    public function getItemsFromCategory($params, Category $category)
    {
        $videos = [];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            return $videos;
        }

        $totalTestedItems = $this->countItems($category->category_id, 1);
        $totalTestItems = $this->countItems($category->category_id, 0);
        $this->totalItems = $totalTestedItems + $totalTestItems;//$this->countTotalItems($category->category_id);

        if ($this->totalItems > 0) {

            $items_per_page = (int) Yii::$app->getModule('videos')->settings->get('items_per_page', self::ITEMS_PER_PAGE);
            $test_item_percent = (int) Yii::$app->getModule('videos')->settings->get('test_items_percent', self::TEST_ITEMS_PERCENT);
            $tested_per_page = ceil(((100 - $test_item_percent) / 100) * $items_per_page);
            $test_per_page = floor(($test_item_percent / 100) * $items_per_page);

                // Если ли вообще у нас на странице тестовые ролики.
            if ($totalTestItems === 0 || $test_per_page === 0) {
                $tested_per_page = $items_per_page;
                $test_per_page = 0;
            }

            if ($totalTestItems > 0 && $totalTestItems < $test_per_page) {
                if ($this->page == 1) {
                    $test_per_page = $totalTestItems;
                    $tested_per_page = $items_per_page - $test_per_page;
                } else {
                    $test_per_page = 0;
                    $tested_per_page = $items_per_page;
                }
            }

                // Проверим, является ли текущая страница валидной.
            $totalPages = ceil($this->totalItems / $items_per_page);
            if ($this->page > $totalPages) {
                throw new NotFoundHttpException();
            }

                // Высчитаем смещение и получим завершившие тест тумбы
            $testedOffset = ($this->page - 1) * $tested_per_page;
            $testedItems = $this->getItems($tested_per_page, $testedOffset, $category->category_id, 1);
            $actuallyTestedImagesNumber = count($testedItems);

                // Если тестовые ролики есть, найдем их и запишем в массив видео.
            if ($totalTestItems > 0 && $test_per_page > 0) {
                $testOffset = ($this->page - 1) * $test_per_page;

                    // Если на странице нехватает завершивших тест, то доберем больше тестовых.
                if ($actuallyTestedImagesNumber < $tested_per_page) {
                        // если завершивших тест вообще нет, увеличим смещение тестовых.
                    if ($actuallyTestedImagesNumber === 0 && $testOffset > 0) {
                        $a = floor($totalTestedItems / $tested_per_page);
                        $b = $totalTestedItems - ($tested_per_page * $a);
                        $testOffset += ($items_per_page - ($test_per_page + $b));
                    }

                        // Доберем тестируемые.
                    $test_per_page = $items_per_page - $actuallyTestedImagesNumber;
                }

                $testItems = $this->getItems($test_per_page, $testOffset, $category->category_id, 0);

                $testedNum = count($testedItems);
                $testNum = count($testItems);

                    // перемешаем тестовые и не тестовые местами
                if (count($testedItems) >= 4 && $testNum > 0) {
                    $videos = [];

                    $totalItemsOnPage = $testedNum + $testNum;
                        // Вычислим места, в которых будут стоять тестовые тумбы.
                    $filledArray = range(0, $totalItemsOnPage - 1);
                    array_splice($filledArray, 0, 5);
                    $randKeys = (array) array_rand($filledArray, $testNum);
                    $testPlacePositions = array_values(array_intersect_key($filledArray, array_flip($randKeys)));

                    $testPlaceIterator = new \ArrayIterator($testPlacePositions);
                    //$testPlaceIterator->rewind();
                    /*$testIterator = new \ArrayIterator($testItems);
                    $testedIterator = new \ArrayIterator($testedItems);

                    for ($i = 0; $i < $totalItemsOnPage; $i++) {
                        if ($i === $testPlaceIterator->current()) {
                            //if ($testIterator->current() != false) {
                                $videos[$testIterator->key()] = $testIterator->current();
                            //}
                            $testIterator->next();
                            $testPlaceIterator->next();
                        } else {
                            //if ($testedIterator->current() != false) {
                                $videos[$testedIterator->key()] = $testedIterator->current();
                            //}
                            $testedIterator->next();
                        }
                    }*/


                    for ($i = 0; $i < $totalItemsOnPage; $i++) {
                        if ($i === $testPlaceIterator->current()) {
                            $video = array_shift($testItems);

                            $testPlaceIterator->next();
                        } else {
                            $video = array_shift($testedItems);
                        }

                        $videos[$video->image_id] = $video;
                    }


                } else {
                    $videos = $testedItems + $testItems;
                }

            } else {
                $videos = $testedItems;
            }
        }

        return $videos;
    }

    private function countTotalItems($category_id)
    {
        $count = RotationStats::find()
            ->joinWith('video')
            ->andWhere([
                'category_id' => $category_id,
                'best_image' => 1,
                'status' => 10,
            ])
            ->count();

        $this->totalItems = $count;

        return $count;
    }

    private function countItems($category_id, $tested = 1)
    {
        $count = RotationStats::find()
            ->joinWith('video')
            ->andWhere([
                'category_id' => $category_id,
                'best_image' => 1,
                'tested_image' => $tested,
                'status' => 10,
            ])
            ->count();

        return (int) $count;
    }

    private function getItems($items_per_page, $offset, $category_id, $tested = null)
    {
        return RotationStats::find()
            ->joinWith('video')
            ->with('image')
            ->with('categories')
            ->andWhere([
                'category_id' =>  $category_id,
                'best_image' => 1,
                'tested_image' => $tested,
                'status' => 10,
            ])
            ->limit($items_per_page)
            ->offset($offset)
            ->orderBy($this->getSort()->getOrders())
            ->indexBy('image_id')
            ->all();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Video::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'video_id' => $this->video_id,
            'image_id' => $this->image_id,
            'user_id' => $this->user_id,
            'orientation' => $this->orientation,
            'duration' => $this->duration,
            'on_index' => $this->on_index,
            'likes' => $this->likes,
            'dislikes' => $this->dislikes,
            'comments_count' => $this->comments_count,
            'views' => $this->views,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'short_description', $this->short_description])
            ->andFilterWhere(['like', 'video_url', $this->video_url])
            ->andFilterWhere(['like', 'embed', $this->embed]);

        return $dataProvider;
    }

    public function getSort()
    {
        if (null === $this->sort) {
            $this->sort = new Sort([
                'attributes' => [
                    'popular' => [
                        //'asc' => ['ctr' => SORT_ASC],
                        'desc' => ['ctr' => SORT_DESC],
                        'default' => SORT_DESC,
                        'label' => 'popular',
                    ],
                    'new' => [
                        'asc' => ['published_at' => SORT_DESC],
                        //'desc' => ['published_at' => SORT_DESC],
                        'default' => SORT_DESC,
                        'label' => 'date',
                    ],
                ],
                'defaultOrder' => [
                    'popular' => SORT_DESC,
                ],
            ]);
        }

        return $this->sort;
    }

    /**
     * Возвращает количество постов последнего запроса.
     *
     * @return int
     */
    public function getTotalItems()
    {
        if ($this->totalItems !== null)
            return (int) $this->totalItems;
        else
            return 0;
    }

    public function getParams()
    {
        return $this->params;
    }
}
