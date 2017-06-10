<?php

namespace backend\modules\videos\models\repositories;

use Yii;

use yii\web\NotFoundHttpException;

use yii\data\ActiveDataProvider;
use yii\data\Sort;

use backend\modules\videos\models\Videos;
use backend\modules\videos\models\VideosStats;
use backend\modules\videos\models\VideosCategories;

/**
 * VideosRepository represents the model behind the search form about `frontend\models\videos\Videos`.
 */
class VideosRepository extends \yii\base\Model
{
	public $slug;
	public $page;

	private $totalItems;

	private $sort;
	protected $params;

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

		$videosSearch = Videos::find()
			->with(['categories' => function ($categoryQquery) {
				$categoryQquery->select(['category_id', 'title', 'slug', 'h1']);
			}])
			->with('image')
			->where(['status' => 10]);

		$counter = clone $videosSearch;
		$this->totalItems = $counter->count();

		$items_per_page = (int) $this->params['items_per_page'];
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
	public function getItemsFromCategory($params, VideosCategories $category)
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

			$items_per_page = (int) $this->params['items_per_page'];
			$tested_per_page = ceil(((100 - $this->params['test_items_percent']) / 100) * $items_per_page);
			$test_per_page = floor(($this->params['test_items_percent'] / 100) * $items_per_page);

				// Если ли вообще у нас на странице тестовые ролики.
			if ($totalTestItems === 0 || $test_per_page === 0) {
				$tested_per_page = $items_per_page;
				$test_per_page = 0;
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

			    $videos = $testedItems + $testItems;

			} else {
				$videos = $testedItems;
			}


	    }

        return $videos;
	}

	private function countTotalItems($category_id)
	{
		$count = VideosStats::find()
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
		$count = VideosStats::find()
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
		$videos = VideosStats::find()
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

        return $videos;
	}

	private function getTestedImages($items_per_page, $category_id)
	{
		$query = VideosStats::find()
			->joinWith('video')
			->with('image')
			->with('categories')
			->andWhere([
	            'category_id' =>  $category_id,
	            'best_image' => 1,
	            'tested_image' => 1,
	            'status' => 10,
	        ]);

			// Постраничная выборка и сортировка
        $offset = ($this->page - 1) * $items_per_page;

        $videos = $query
            ->limit($items_per_page)
            ->offset($offset)
            ->orderBy($this->getSort()->getOrders())
            ->indexBy('image_id')
            ->all();

        return $videos;
	}

	private function getTestImages($items_per_page, $category_id)
	{
		$query = VideosStats::find()
			->joinWith('video')
			->with('image')
			->with('categories')
			->andWhere([
	            'category_id' =>  $category_id,
	            'best_image' => 1,
	            'tested_image' => 0,
	            'status' => 10,
	        ]);

			// Постраничная выборка и сортировка
        $offset = ($this->page - 1) * $items_per_page;

        $videos = $query
            ->limit($items_per_page)
            ->offset($offset)
            ->orderBy($this->getSort()->getOrders())
            ->indexBy('image_id')
            ->all();

        return $videos;
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
        $query = Videos::find();

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
