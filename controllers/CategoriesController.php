<?php

namespace ytubes\admin\videos\controllers;

use Yii;

use yii\base\DynamicModel;

use yii\di\Instance;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;

use yii\data\ActiveDataProvider;

use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;


use ytubes\admin\videos\models\VideosCategories;
use ytubes\admin\videos\models\forms\CategoriesImport;
use ytubes\admin\videos\models\repositories\CategoriesRepository;

/**
 * CategoriesController implements the CRUD actions for VideosCategories model.
 */
class CategoriesController extends \yii\web\Controller
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
                    'delete' => ['POST'],
                    'save-order' => ['POST'],
                ],
            ],
	        'contentNegotiator' => [
	            'class' => ContentNegotiator::className(),
	            'only' => ['save-order'],
	            'formats' => [
	                'application/json' => \yii\web\Response::FORMAT_JSON,
	            ],
	        ],
        ];
    }

    /**
     * Lists all VideosCategories models.
     * @return mixed
     */
    public function actionIndex()
    {
        $createModel = new VideosCategories();
        $categories = VideosCategories::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        $dataProvider = new ActiveDataProvider([
            'query' => VideosCategories::find(),
			'pagination' => [
				'pageSize' => 500,
			],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'createModel' => $createModel,
        ]);
    }

    /**
     * Displays a single VideosCategories model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $categories = VideosCategories::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        $model = CategoriesRepository::findById($id);

        if (!$model instanceof VideosCategories) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    /**
     * Creates a new VideosCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new VideosCategories();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->category_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing VideosCategories model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = CategoriesRepository::findById($id);

        if (!$model instanceof VideosCategories) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }

        $categories = VideosCategories::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', 'Новые данные для категории сохранены');
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    /**
     * Deletes an existing VideosCategories model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $category = CategoriesRepository::findById($id);

        if (!$category instanceof VideosCategories) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }
        $title = $category->title;

        if ($category->delete()) {
        	Yii::$app->getSession()->setFlash('success', "Категория \"<b>{$title}</b>\" успешно удалена");
        } else {
        	Yii::$app->getSession()->setFlash('error', "Удалить категрию \"<b>{$title}</b>\" не удалось");
        }

        return $this->redirect(['index']);
    }

    /**
     * Displays a single VideosCategories model.
     * @param integer $id
     * @return mixed
     */
    public function actionImport()
    {
        $model = new CategoriesImport();

        if ($model->load([$model->formName() => $this->request->post()]) && $model->save()) {
            Yii::warning('imported');
        }

        return $this->render('import', [
        	'model' => $model,
        ]);
    }

    /**
     * Сохраняет порядок сортировки категорий, установленный пользователем.
     * @return mixed
     */
    public function actionSaveOrder()
    {

			// Валидация массива идентификаторов категорий.
		$validationModel = DynamicModel::validateData(['categories_ids' => $this->request->post('order')], [
            ['categories_ids', 'each', 'rule' => ['integer']],
			['categories_ids', 'filter', 'filter' => 'array_filter'],
			['categories_ids', 'required', 'message' => 'Categories not select'],
		]);

		if ($validationModel->hasErrors()) {
			return [
				'status' => 'error',
				'error' => 'Ошибка валидации',
			];
		}

		$db = Yii::$app->db;
		$transaction = $db->beginTransaction();

		try {
			$categories_ids_list = implode(',', $validationModel->categories_ids);

			$sql = "UPDATE {{" . VideosCategories::tableName() . "}}
					SET `position` = FIND_IN_SET(`category_id`, '{$categories_ids_list}')
					WHERE FIND_IN_SET(`category_id`, '{$categories_ids_list}')!=0";

		    $db->createCommand($sql)->execute();
		    $transaction->commit();

			return [
		    	'status' => 'success',
		    	'message' => 'Порядок сортировки категорий сохранен'
		    ];
		} catch (\Exception $e) {
		    $transaction->rollBack();

		    return [
		    	'status' => 'error',
		    	'message' => $e->getMessage()
		    ];
		}
    }

    /**
     * Finds the VideosCategories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VideosCategories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VideosCategories::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
