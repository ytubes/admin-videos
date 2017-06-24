<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Импорт';
$this->params['subtitle'] = 'Добавить настройку импорта видео';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['videos']];
$this->params['breadcrumbs'][] = 'Добавить настройку импорта видео';

?>

<section class="content">

	<div class="row">
		<div class="col-md-12">

			<div class="box box-success">
				<div class="box-header with-border">
					<i class="fa  fa-file-o"></i><h3 class="box-title">Добавить настройку импорта видео</h3>
	            </div>

				<?php $form = ActiveForm::begin(); ?>

		            <div class="box-body pad">

					    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

						<h4>Настройки ввода</h4>
						<div class="row">
							<div class="col-md-3 form-group">
								<label class="control-label" style="display:block;">Добавить\удалить поля</label>
								<div class="btn-group">
	                    			<button type="button" id="add_field" class="btn btn-default"><i class="fa fa-plus"></i></button>
	                    			<button type="button" id="remove_field" class="btn btn-default"><i class="fa fa-minus"></i></button>
	                    		</div>
							</div>
							<div class="col-md-2 form-group">
								<label class="control-label">Разделитель</label>
								<?= Html::activeInput('text', $model, 'delimiter', ['class' => 'form-control']) ?>
                			</div>
							<div class="col-md-2 form-group">
								<label class="control-label">Ограничитель поля</label>
								<?= Html::activeInput('text', $model, 'enclosure', ['class' => 'form-control']) ?>
                			</div>
						</div>

						<h4>Поля csv</h4>
						<div class="row csv-fields">
							<?php foreach ($model->fields as $field): ?>
								<div class="col-md-2 form-group">
									<?= Html::dropDownList('ImportFeed[fields][]', '', $model->getFieldsOptions(), ['class' => 'form-control']) ?>
								</div>
							<?php endforeach; ?>
						</div>

					    <?= $form->field($model, 'skip_duplicate_urls')->checkbox() ?>

					    <?= $form->field($model, 'skip_duplicate_embeds')->checkbox() ?>

					    <?= $form->field($model, 'skip_new_categories')->checkbox() ?>

					    <?= $form->field($model, 'external_images')->checkbox() ?>

					    <?= $form->field($model, 'template')->textInput(['maxlength' => true]) ?>

					</div>


					<div class="box-footer clearfix">
					    <div class="form-group">
							<?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
							<?= Html::a('К списку', ['list-feeds'], ['class' => 'btn btn-warning']) ?>
						</div>
					</div>

				<?php ActiveForm::end(); ?>

			</div>

		</div>
	</div>
</section>

<?php

$rowOptions = [];
foreach ($model->getFieldsOptions() as $key => $val) {
	$rowOptions[] = [
		'value' => $key,
		'text' => $val,
	];
}

$encodedOptions = json_encode($rowOptions);
$this->registerJS("var csvSelectOptions = {$encodedOptions};", \yii\web\View::POS_HEAD, 'csvSelectOptions');

$js = <<< 'JS'
	$('#add_field').click(function() {
		var tagDiv = $('<div/>', {
		    class: 'col-md-2 form-group'
		});
		var tagSelect = $('<select/>', {
		    class: 'form-control',
		    name: 'ImportFeed[fields][]'
		});

		$(csvSelectOptions).each(function() {
			tagSelect.append($('<option>').attr('value',this.value).text(this.text));
		});

		tagSelect.appendTo(tagDiv);
		tagDiv.appendTo('.csv-fields');
	});
	$('#remove_field').click(function(){
		var fields_container = $('.csv-fields div');
		var childs_num = fields_container.children().length;

		if (childs_num > 1) {
			fields_container.last().remove();
		}
	});
JS;

$this->registerJS($js);
?>
