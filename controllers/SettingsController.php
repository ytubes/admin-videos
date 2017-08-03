<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\di\Instance;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use ytubes\videos\admin\models\Settings;

/**
 * VideosController implements the CRUD actions for Videos model.
 */
class SettingsController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * List base Settings and save it.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Settings();

        if ($model->load($this->request->post()) && $model->save()) {
            $this->session->setFlash('info', 'Новые настройки сохранены');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
