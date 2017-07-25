<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\di\Instance;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

use ytubes\videos\admin\models\forms\Tools;

/**
 * ToolsController это всякие инструменты.
 */
class ToolsController extends Controller
{
    public $request = 'request';
    public $response = 'response';

    public function init()
    {
        parent::init();
        	// Инжект request и response
        $this->request = Instance::ensure($this->request, Request::className());
        $this->response =Instance::ensure($this->response, Response::className());
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
	                'application/json' => Response::FORMAT_JSON,
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
