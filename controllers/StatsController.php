<?php
namespace ytubes\videos\admin\controllers;

use Yii;

use yii\web\NotFoundHttpException;

use ytubes\videos\admin\models\RotationStats;
use ytubes\videos\admin\models\Category;

class StatsController extends \yii\web\Controller
{
    public $request = 'request';
    public $response = 'response';

    public function init()
    {
        parent::init();
        	// Инжект request и response
        $this->request = \yii\di\Instance::ensure($this->request, \yii\web\Request::className());
        $this->response = \yii\di\Instance::ensure($this->response, \yii\web\Response::className());
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
	       'access' => [
	           'class' => \yii\filters\AccessControl::className(),
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
                'class' => \yii\filters\VerbFilter::className(),
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
