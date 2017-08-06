<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Категории видео';
$this->params['subtitle'] = 'Редактирование';

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';

?>

<div class="row">

	<div class="col-md-4">
		<?= $this->render('_left_sidebar', [
			'categories' => $categories,
			'active_id' => isset($model)? $model->category_id : 0,
		]) ?>
	</div>

	<div class="col-md-8">

		<div class="box box-primary">
			<div class="box-header with-border">
				<i class="fa fa-edit"></i><h3 class="box-title">Редактирование: <?= $model->title ?></h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i>', ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
						<?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['index'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить категорию']) ?>
						<?= Html::a('<i class="fa fa-info-circle" style="color:#337ab7;"></i>', ['view', 'id' => $model->category_id], ['class' => 'btn btn-default btn-sm', 'title' => 'Информация о категории']) ?>
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

			<?php $form = ActiveForm::begin(); ?>

	            <div class="box-body pad">

					<?= $form->field($model, 'position')->textInput() ?>

					<?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'meta_title')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'meta_description')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'h1')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'seotext')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'param1')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'param2')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'param3')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'on_index')->checkbox() ?>

					<?= $form->field($model, 'reset_clicks_period')->textInput() ?>

				</div>


				<div class="box-footer clearfix">
				    <div class="form-group">
						<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
					</div>
				</div>

			<?php ActiveForm::end(); ?>

		</div>

	</div>
</div>
