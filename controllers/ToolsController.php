<?php

namespace backend\modules\videos\controllers;

use Yii;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;

use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;


use backend\models\videos\VideosCategories;
use backend\models\videos\forms\CategoriesImport;
use backend\models\videos\forms\Tools;

/**
 * ToolsController implements the CRUD actions for Tools model.
 */
class ToolsController extends \yii\web\Controller
{
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
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionClearStats()
    {
        $request = Yii::$app->getRequest();
        $model = new Tools();

        if ($model->load([$model->formName() => $request->post()]) && $model->clearStats()) {
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
        $request = Yii::$app->getRequest();
        $model = new Tools();

        if ($model->load([$model->formName() => $request->post()]) && $model->randomDate()) {
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
        $request = Yii::$app->getRequest();
        $model = new Tools();

        if ($model->load([$model->formName() => $request->post()]) && $model->clearVideos()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }
}
