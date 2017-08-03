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
        $this->request = Instance::ensure($this->request, Request::class);
        $this->response =Instance::ensure($this->response, Response::class);
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
                    'clear-stats' => ['POST'],
                    'random-date' => ['POST'],
                    'clear-videos' => ['POST'],
                    'clear-related' => ['POST'],
                    'recalculate-categories-videos' => ['POST'],
                    'set-categories-thumbs' => ['POST'],
                ],
            ],
	        'contentNegotiator' => [
	            'class' => ContentNegotiator::class,
	            'only' => [
	            	'clear-stats',
	            	'random-date',
	            	'clear-videos',
	            	'clear-related',
	            	'recalculate-categories-videos',
	            	'set-categories-thumbs',
	            ],
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
     * @return json
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
     * @return json
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
     * Удаляет все видео.
     *
     * @return json
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
    /**
     * Очищает таблицу "похожие видео".
     *
     * @return json
     */
    public function actionClearRelated()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->clearRelated()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
    /**
     * Очищает таблицу "похожие видео".
     *
     * @return json
     */
    public function actionRecalculateCategoriesVideos()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->recalculateCategoriesVideos()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
    /**
     * Установка тумб у категорий по данным ротации
     *
     * @return json
     */
    public function actionSetCategoriesThumbs()
    {
        $model = new Tools();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->setCategoriesThumbs()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
}
