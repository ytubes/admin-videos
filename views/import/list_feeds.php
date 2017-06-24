<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Импорт';
$this->params['subtitle'] = 'Список фидов';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['import/videos']];
$this->params['breadcrumbs'][] = 'Список фидов';

?>


<section class="content">

	<div class="row">
		<div class="col-md-12">

			<div class="box box-default">
				<div class="box-header with-border">
					<i class="fa fa-list"></i><h3 class="box-title">Фиды импорта</h3>
					<div class="box-tools pull-right">
						<div class="btn-group">
							<?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['add-feed'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить новый фид']) ?>
						</div>
					</div>
	            </div>

	            <div class="box-body pad">
				    <?= GridView::widget([
				        'dataProvider' => $dataProvider,
				        'columns' => [
				            [
				            	'attribute' => 'feed_id',
				            	'contentOptions'=> ['style'=>'width: 90px;'],
				            ],
				            [
				            	'attribute' => 'name',
				            	'format' => 'ntext',
				            	'contentOptions'=> ['style'=>'width: 130px;'],
				            ],
				            'description:ntext',
				            [
				            	'class' => 'yii\grid\ActionColumn',
				            	'template' => '{update} {delete}',
						        'urlCreator' => function ($action, $model, $key, $index) {
						            if ($action === 'view') {
						                return Url::to(['import/view-feed', 'id' => $model->feed_id]);
						            }
						            if ($action === 'update') {
						                 return Url::to(['import/update-feed', 'id' => $model->feed_id]);
						            }
						            if ($action === 'delete') {
						                return Url::to(['import/delete-feed', 'id' => $model->feed_id]);
						            }

						        },
						        'contentOptions'=> ['style'=>'width: 60px;'],
				            ],
				        ],
				    ]); ?>

				</div>

			</div>

		</div>
	</div>
</section>
