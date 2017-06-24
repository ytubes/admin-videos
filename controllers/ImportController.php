<?php

namespace ytubes\admin\videos\controllers;

use Yii;

use yii\di\Instance;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;

use yii\data\ActiveDataProvider;

use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

use ytubes\admin\videos\models\forms\CategoriesImport;
use ytubes\admin\videos\models\forms\VideosImport;
use ytubes\admin\videos\models\ImportFeed;

/**
 * CategoriesController implements the CRUD actions for VideosCategories model.
 */
class ImportController extends \yii\web\Controller
{
    public $request = 'request';
    public $response = 'response';

    public function init()
    {
        parent::init();
        	// Инжект request и response
        $this->request = Instance::ensure($this->request, Request::className());
        $this->response = Instance::ensure($this->response, Response::className());
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
                    'delete-feed' => ['POST'],
                ],
            ],
        ];
    }


    /**
     * Импорт роликов через файл или текстовую форму
     * @return mixed
     */
    public function actionVideos($preset = 0)
    {
        $importFeed = ImportFeed::find()
        	->where(['feed_id' => $preset])
        	->one();
        if (!$importFeed instanceof ImportFeed) {
        	$importFeed = new ImportFeed();
        }

        $model = new VideosImport($importFeed);

        if ($model->load([$model->formName() => Yii::$app->request->post()]) && $model->save()) {
            if ($model->imported_rows_num > 0) {
            	Yii::$app->getSession()->setFlash('success', "<b>{$model->imported_rows_num}</b> роликов успешно добавлено");
            }
        }

        return $this->render('videos', [
        	'preset' => $preset,
        	'model' => $model,
        ]);
    }

    /**
     * Displays a single VideosCategories model.
     * @param integer $id
     * @return mixed
     */
    public function actionCategories()
    {
        $model = new CategoriesImport();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->save()) {
            Yii::warning('imported');
        }

        return $this->render('categories', [
        	'model' => $model,
        ]);
    }

    /**
     * Lists all ImportFeed models.
     * @return mixed
     */
    public function actionListFeeds()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportFeed::find(),
			'pagination' => [
				'pageSize' => 500,
			],
        ]);

        return $this->render('list_feeds', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddFeed()
    {
        $model = new ImportFeed();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['videos', 'preset' => $model->feed_id]);
        } else {
            return $this->render('add_feed', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdateFeed($id)
    {
        $model = ImportFeed::findById($id);

        if (!$model instanceof ImportFeed) {
        	throw new NotFoundHttpException('The requested feed does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['videos', 'preset' => $model->feed_id]);
        } else {
            return $this->render('update_feed', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionDeleteFeed($id)
    {
        $model = ImportFeed::findById($id);

        if (!$model instanceof ImportFeed) {
        	throw new NotFoundHttpException('The requested feed does not exist.');
        }

		$name = $model->name;

        if ($model->delete()) {
        	Yii::$app->getSession()->setFlash('success', "Фид \"<b>$name</b>\" успешно удален.");
            return $this->redirect(['list-feeds']);
        }
    }
}
