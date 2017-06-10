<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Категории видео';
$this->params['subtitle'] = 'Добавить новую категорию';

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Создание';

?>

<section class="content">

	<div class="row">
		<div class="col-md-12">

			<div class="box box-success">
				<div class="box-header with-border">
					<i class="fa  fa-file-o"></i><h3 class="box-title">Добавить новую категорию</h3>
					<div class="box-tools pull-right">
						<div class="btn-group">
							<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i>', ['import'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
						</div>
					</div>
	            </div>

				<?php $form = ActiveForm::begin(); ?>

		            <div class="box-body pad">

					    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'meta_title')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'meta_description')->textInput(['maxlength' => true]) ?>

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
							<?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
							<?= Html::a('Назад', ['index'], ['class' => 'btn btn-warning']) ?>
						</div>
					</div>

				<?php ActiveForm::end(); ?>

			</div>

		</div>
	</div>
</section>

