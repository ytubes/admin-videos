<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\di\Instance;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;
use yii\web\Controller;

use ytubes\videos\models\Category;
use ytubes\videos\admin\models\forms\CategoriesImport;
use ytubes\videos\admin\models\finders\CategoryFinder;

/**
 * CategoriesController implements the CRUD actions for Category model.
 */
class CategoriesController extends Controller
{
    public $request = 'request';
    public $response = 'response';
    public $session = 'session';

    public function init()
    {
        parent::init();
        	// Инжект request и response
        $this->request = Instance::ensure($this->request, Request::class);
        $this->response = Instance::ensure($this->response, Response::class);
        $this->session = Instance::ensure($this->session, Session::class);
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
                    'save-order' => ['POST'],
                ],
            ],
	        'contentNegotiator' => [
	            'class' => ContentNegotiator::class,
	            'only' => ['save-order'],
	            'formats' => [
	                'application/json' => Response::FORMAT_JSON,
	            ],
	        ],
        ];
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $createModel = new Category();
        $categories = Category::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        $dataProvider = new ActiveDataProvider([
            'query' => Category::find(),
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
     * Displays a single Category model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $categories = Category::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        $model = CategoryFinder::findById($id);

        if (!$model instanceof Category) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }
    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = CategoryFinder::findById($id);

        if (!$model instanceof Category) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }

        $categories = Category::find()
        	->orderBy(['position' => SORT_ASC])
        	->all();

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('success', 'Новые данные для категории сохранены');
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }
    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $category = CategoryFinder::findById($id);

        if (!$category instanceof Category) {
        	throw new NotFoundHttpException('The requested category does not exist.');
        }
        $title = $category->title;

        if ($category->delete()) {
        	$this->session->setFlash('success', "Категория \"<b>{$title}</b>\" успешно удалена");
        } else {
        	$this->session->setFlash('error', "Удалить категрию \"<b>{$title}</b>\" не удалось");
        }

        return $this->redirect(['index']);
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

			$sql = "UPDATE `" . Category::tableName() . "`
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
}
