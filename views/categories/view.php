<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Категории видео';
$this->params['subtitle'] = 'Информация';

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Информация';

?>

<div class="row">

	<div class="col-md-4">
		<?= $this->render('_left_sidebar', [
			'categories' => $categories,
			'active_id' => isset($model)? $model->category_id : 0,
		]) ?>
	</div>

	<div class="col-md-8">
		<div class="box box-info">
			<div class="box-header with-border">
				<i class="fa fa-info-circle"></i><h3 class="box-title">Информация: <?= $model->title ?></h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i>', ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
						<?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['index'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить категорию']) ?>
						<?= Html::a('<i class="fa fa-edit" style="color:#337ab7;"></i>', ['update', 'id' => $model->category_id], ['class' => 'btn btn-default btn-sm', 'title' => 'Редактировать категории']) ?>
						<?= Html::a('<i class="fa fa-trash-o" style="color:brown;"></i>', ['delete', 'id' => $model->category_id], [
				            'class' => 'btn btn-default btn-sm',
				            'title' => 'Удалить категорию',
				            'data' => [
				                'confirm' => 'Действительно хотите удалить эту категорию?',
				                'method' => 'post',
				            ],
				        ]) ?>
					</div>
				</div>
            </div>

            <div class="box-body pad">

			    <?= DetailView::widget([
			        'model' => $model,
			        'attributes' => [
			            'category_id',
			            'position',
			            'slug',
			            'image',
			            'meta_title',
			            'meta_description',
			            'title',
			            'h1',
			            'description:ntext',
			            'seotext:ntext',
			            'param1:ntext',
			            'param2:ntext',
			            'param3:ntext',
			            'videos_num',
			            'on_index',
			            'shows',
			            'clicks',
			            'ctr',
			            'reset_clicks_period',
			            'created_at',
			            'updated_at',
			        ],
			    ]) ?>

			</div>

		</div>

	</div>
</div>
