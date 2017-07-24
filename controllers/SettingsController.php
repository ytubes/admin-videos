<?php
namespace ytubes\videos\admin\controllers;

use Yii;
use yii\web\NotFoundHttpException;

use ytubes\videos\admin\models\Settings;

/**
 * VideosController implements the CRUD actions for Videos model.
 */
class SettingsController extends \yii\web\Controller
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

    /**
     * Lists all Setting models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Settings();

        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('info', 'Сохранилось');
            $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
