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
use yii\web\Session;

use ytubes\videos\models\VideoStatus;
use ytubes\videos\admin\models\forms\MassActions;

use common\models\users\User;

/**
 * ToolsController это всякие инструменты.
 */
class MassActionsController extends Controller
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
                    'change-status' => ['GET', 'POST'],
                    'change-user' => ['GET', 'POST'],
                    'delete-videos' => ['POST'],
                    //'clear-videos' => ['POST'],
                    //'clear-related' => ['POST'],
                    //'recalculate-categories-videos' => ['POST'],
                    //'set-categories-thumbs' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Выводит форму с различными действиями для видео роликов.
     * @return mixed
     */
    public function actionChangeStatus()
    {
		if ($this->request->isPost) {
			$model = new MassActions(['scenario' => MassActions::SCENARIO_CHANGE_STATUS]);
			$this->response->format = Response::FORMAT_JSON;

	        if ($model->load([$model->formName() => $this->request->post()]) && $model->changeStatus()) {
	        	$this->session->setFlash('success', 'Статус у ' . $model->getChangedRowsNum() . ' видео изменен на: ' . $model->getStatusLabel());
	            return ['status' => 'success'];
	        } else {
	            return ['status' => 'error'];
	        }
		}

		$listStatus = VideoStatus::listStatus();

        return $this->renderAjax('change_status', [
            'listStatus' => $listStatus,
        ]);
    }
    /**
     * Выводит форму с различными действиями для видео роликов.
     * @return mixed
     */
    public function actionChangeUser()
    {
		if ($this->request->isPost) {
			$model = new MassActions(['scenario' => MassActions::SCENARIO_CHANGE_USER]);
			$this->response->format = Response::FORMAT_JSON;

	        if ($model->load([$model->formName() => $this->request->post()]) && $model->changeUser()) {
	        	$this->session->setFlash('success', 'Автор у ' . $model->getChangedRowsNum() . ' видео изменен на: ' . $model->getUsername());
	            return ['status' => 'success'];
	        } else {
	            return ['status' => 'error'];
	        }
		}

		$listUser = User::listUser();

        return $this->renderAjax('change_user', [
            'listUser' => $listUser,
        ]);
    }
    /**
     * Удаляет видео
     * @return json
     */
    public function actionDeleteVideos()
    {
		$model = new MassActions();
		$this->response->format = Response::FORMAT_JSON;

        if ($model->load([$model->formName() => $this->request->post()]) && $model->deleteVideos()) {
        	$this->session->setFlash('success', 'Удалено ' . $model->getChangedRowsNum() . ' видео');
            return ['status' => 'success'];
        } else {
            return ['status' => 'error'];
        }
    }
}
