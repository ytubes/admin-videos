<?php
namespace ytubes\videos\admin\controllers;

use Yii;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;

use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;


use ytubes\videos\admin\models\VideosCategories;
use ytubes\videos\admin\models\forms\CategoriesImport;
use ytubes\videos\admin\models\forms\Tools;

/**
 * ToolsController implements the CRUD actions for Tools model.
 */
class ToolsController extends \yii\web\Controller
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
	           'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'clear-stats' => ['POST'],
                    'random-date' => ['POST'],
                    'clear-videos' => ['POST'],
                ],
            ],
	        'contentNegotiator' => [
	            'class' => ContentNegotiator::className(),
	            'only' => ['clear-stats', 'random-date', 'clear-videos'],
	            'formatParam' => '_format',
	            'formats' => [
	                'application/json' => \yii\web\Response::FORMAT_JSON,
	            ],
	        ],
        ];
    }

    /**
     * Выводит форму с различными действиями для видео роликов.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Tools();

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Очищает статистику по видео (показы, просмотры и т.д.)
     * @return mixed
     */
    public function actionClearStats()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->clearStats()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionRandomDate()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->randomDate()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionClearVideos()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->clearVideos()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
}
