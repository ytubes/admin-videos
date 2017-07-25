<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\di\Instance;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

use ytubes\videos\admin\models\forms\CategoriesImport;
use ytubes\videos\admin\models\forms\VideosImport;
use ytubes\videos\admin\models\ImportFeed;

/**
 * CategoriesController implements the CRUD actions for VideosCategories model.
 */
class ImportController extends Controller
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

        if ($model->load([$model->formName() => $this->request->post()]) && $model->save()) {
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
     * Импорт категорий через файл или текстовую форму.
     * @return mixed
     */
    public function actionCategories()
    {
        $model = new CategoriesImport();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->save()) {
            if ($model->imported_rows_num > 0) {
            	Yii::$app->getSession()->setFlash('success', "<b>{$model->imported_rows_num}</b> категорий добавлено или обновлено");
            }
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
     * Creates a new ImportFeed model.
     * If creation is successful, the browser will be redirected to the 'videos' page.
     * @return mixed
     */
    public function actionAddFeed()
    {
        $model = new ImportFeed();

        if ($model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['videos', 'preset' => $model->feed_id]);
        } else {
            return $this->render('add_feed', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Редактирование существующего фида импорта
     * @return mixed
     */
    public function actionUpdateFeed($id)
    {
        $model = ImportFeed::findById($id);

        if (!$model instanceof ImportFeed) {
        	throw new NotFoundHttpException('The requested feed does not exist.');
        }

        if ($model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['videos', 'preset' => $model->feed_id]);
        } else {
            return $this->render('update_feed', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Удаление фида импорта
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
