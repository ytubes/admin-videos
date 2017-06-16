<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Settings';
$this->params['breadcrumbs'][] = $this->title;
?>

<section class="content">

	<div class="row">

		<div class="col-md-3">
			<?= \backend\widgets\SettingsMenu::widget(); ?>
		</div>

		<div class="col-md-9">
			<div class="box box-primary">
				<div class="box-header with-border">
					<i class="fa fa-edit"></i><h3 class="box-title">хз че тут</h3>
	            </div>

				<?php $form = ActiveForm::begin([
					'action' => Url::to(['index']),
				]); ?>

		            <div class="box-body pad">
					    <?= $form->field($model, 'items_per_page')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'pagination_buttons_count')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'recalculate_ctr_period')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'related_number')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'test_item_period')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'test_items_percent')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($model, 'test_items_start')->textInput(['maxlength' => true]) ?>

					</div>

					<div class="box-footer clearfix">
					    <div class="form-group">
							<?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
						</div>
					</div>

				<?php ActiveForm::end(); ?>

			</div>

		</div>
	</div>
</section>
