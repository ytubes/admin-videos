<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\di\Instance;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

use ytubes\videos\models\RotationStats;
use ytubes\videos\models\Category;

class StatsController extends Controller
{
    public $request = 'request';
    public $response = 'response';

    public function init()
    {
        parent::init();
        	// Инжект request и response
        $this->request = Instance::ensure($this->request, Request::class);
        $this->response = Instance::ensure($this->response, Response::class);
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
	       'access' => [
	           'class' => AccessControl::class,
               'rules' => [
                   [
                       'allow' => true,
                       'roles' => ['@'],
                       /*'matchCallback' => function ($rule, $action) {
                           return Yii::$app->user->identity->isAdmin;
                       }*/
                   ],
               ],
	       ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
		$data = [
			'total_rows' => RotationStats::find()->count(),
			'tested_rows' => RotationStats::find()->where(['tested_image' => 1])->count(),
			'test_rows' => RotationStats::find()->where(['tested_image' => 0])->count(),
			'tested_zero_ctr' => RotationStats::find()->where(['tested_image' => 1, 'ctr' => 0])->count(),
		];


			// Статистика по категориям.
		$categories = Category::find()
			->indexBy('category_id')
			->all();

		/*SELECT `category_id`, COUNT(*) as `cnt`
		FROM `videos_stats`
		WHERE `category_id`=2 AND `tested_image`=0
		GROUP BY `category_id`*/

		$totalItems = Yii::$app->db->createCommand('SELECT `category_id`, COUNT(*) as `cnt` FROM `videos_stats` GROUP BY `category_id`')
            ->queryAll();
        $totalItems = array_column($totalItems, 'cnt', 'category_id');

		$testedItems = Yii::$app->db->createCommand('SELECT `category_id`, COUNT(*) as `cnt` FROM `videos_stats` WHERE `tested_image`=1 GROUP BY `category_id`')
            ->queryAll();
        $testedItems = array_column($testedItems, 'cnt', 'category_id');

		$testItems = Yii::$app->db->createCommand('SELECT `category_id`, COUNT(*) as `cnt` FROM `videos_stats` WHERE `tested_image`=0 GROUP BY `category_id`')
            ->queryAll();
        $testItems = array_column($testItems, 'cnt', 'category_id');

		$data['categories'] = [];
		foreach ($categories as $category) {
			$category_id = $category->category_id;

			$data['categories'][$category_id] = [
				'category_id' => $category_id,
				'title' => $category->title,
				'slug' => $category->slug,
				'total_rows' => isset($totalItems[$category_id]) ? $totalItems[$category_id] : 0,
				'tested_rows' => isset($testedItems[$category_id]) ? $testedItems[$category_id] : 0,
				'test_rows' => isset($testItems[$category_id]) ? $testItems[$category_id] : 0,
			];
		}

		$data['total_categories'] = Category::find()->count();


        return $this->render('index', [
        	'data' => $data,
        ]);
    }
}
