<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Категории видео';
$this->params['breadcrumbs'][] = $this->title;

// "jquery sort elements" поиск в гугле, для сортировки элементов при помощи жс.
// Также в импорте категорий нужно сохранять выбранные элементы при помощи локал стораджа

?>

<section class="content">

	<div class="row">

		<div class="col-md-4">
			<?= $this->render('_left_sidebar', [
				'categories' => $categories,
				'active_id' => 0,
			]) ?>
		</div>

		<div class="col-md-8">
			<div class="box box-success">
				<div class="box-header with-border">
					<i class="fa fa-file-o"></i><h3 class="box-title">Добавить новую категорию</h3>
					<div class="box-tools pull-right">
						<div class="btn-group">
							<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i>', ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
						</div>
					</div>
	            </div>

				<?php $form = ActiveForm::begin([
					'action' => Url::to(['create']),
				]); ?>

		            <div class="box-body pad">

					    <?= $form->field($createModel, 'title')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($createModel, 'slug')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($createModel, 'meta_title')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($createModel, 'meta_description')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($createModel, 'h1')->textInput(['maxlength' => true]) ?>

					    <?= $form->field($createModel, 'description')->textarea(['rows' => 6]) ?>

					    <?= $form->field($createModel, 'seotext')->textarea(['rows' => 6]) ?>

					    <?= $form->field($createModel, 'param1')->textarea(['rows' => 6]) ?>

					    <?= $form->field($createModel, 'param2')->textarea(['rows' => 6]) ?>

					    <?= $form->field($createModel, 'param3')->textarea(['rows' => 6]) ?>

					    <?= $form->field($createModel, 'on_index')->checkbox() ?>

					    <?= $form->field($createModel, 'reset_clicks_period')->textInput() ?>

					</div>


					<div class="box-footer clearfix">
					    <div class="form-group">
							<?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
						</div>
					</div>

				<?php ActiveForm::end(); ?>

			</div>

		</div>
	</div>
</section>
