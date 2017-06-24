<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Импорт';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Видео';

?>

<section class="content">

	<div class="row">
		<div class="col-md-12">

			<div class="box box-primary">
				<div class="box-header with-border">
					<i class="glyphicon glyphicon-import"></i><h3 class="box-title">Импорт видео</h3>
					<div class="box-tools pull-right">
						<div class="btn-group">
							<?php $form = ActiveForm::begin([
									'options' => [
										'name' => 'preset-select'
									],
									'method' => 'get',
									'action' => ['import/videos'],
								]); ?>
								Настройки импорта: <?= Html::dropDownList('preset', $preset, $model->getPresetOptions(), [
									'id' => 'preset',
									'class' => 'btn-default btn-sm',
								]) ?>
							<?php ActiveForm::end(); ?>
						</div>
						<div class="btn-group">
							<?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['add-feed'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить новый фид']) ?>
							<?php if ($preset > 0): ?>
								<?= Html::a('<i class="fa fa-edit" style="color:#337ab7;"></i>', ['update-feed', 'id' => $preset], ['class' => 'btn btn-default btn-sm', 'title' => 'Редактировать фид']) ?>
								<?= Html::a('<i class="fa fa-trash-o" style="color:brown;"></i>', ['delete-feed', 'id' => $preset], [
						            'class' => 'btn btn-default btn-sm',
						            'title' => 'Удалить фид',
						            'data' => [
						                'confirm' => 'Действительно хотите удалить этот фид?',
						                'method' => 'post',
						            ],
						        ]) ?>
						    <?php endif; ?>
						</div>
					</div>
	            </div>

				<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

		            <div class="box-body pad">
						<?php if ($model->hasErrors('csv_rows')): ?>
							<div class="alert alert-danger alert-dismissible">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
								<h4><i class="icon fa fa-exclamation-circle"></i> Следующие записи не были добавлены: </h4>
								<ul>
								<?php foreach ($model->getErrors('csv_rows') as $error): ?>
									<li><?= $error ?></li>
								<?php endforeach ?>
								</ul>
							</div>
						<?php endif; ?>

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
								<?= Html::activeInput('text', $model, 'delimiter', ['name' => 'delimiter', 'class' => 'form-control']) ?>
                			</div>
							<div class="col-md-2 form-group">
								<label class="control-label">Ограничитель поля</label>
								<?= Html::activeInput('text', $model, 'enclosure', ['name' => 'enclosure', 'class' => 'form-control']) ?>
                			</div>
						</div>


						<h4>Поля csv</h4>
						<div class="row csv-fields">
							<?php foreach ($model->fields as $field): ?>
								<div class="col-md-2 form-group">
									<?= Html::dropDownList('fields[]', $field, $model->getOptions(), ['class' => 'form-control']) ?>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="row">
							<div class="col-md-12 form-group">
								<label class="control-label" for="csv-rows">Данные для вставки (содержимое csv)</label>
								<?= Html::activeTextarea($model, 'csv_rows', ['id' => 'csv-rows', 'name' => 'csv_rows', 'class' => 'form-control', 'rows' => 6]) ?>
								<div class="help-block"></div>
							</div>

							<div class="col-md-12 form-group">
								<label for="csv_file">Файл импорта</label>
								<?= Html::fileInput('csv_file', null, ['id' => 'csv_file']) ?>
								<p class="help-block">Убедитесь в соответствии полей файла с текущими настройками.</p>
							</div>

							<div class="col-md-12 form-group">
								<label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_duplicate_urls', ['name' => 'skip_duplicate_urls', 'label' => false]) ?> <span>Пропускать видео с повторяющимися URL-ами источника</span></label>
								<label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_duplicate_embeds', ['name' => 'skip_duplicate_embeds', 'label' => false]) ?> <span>Пропускать видео с повторяющимися embed кодами</span></label>
							</div>
							<div class="col-md-12 form-group">
								<label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_new_categories', ['name' => 'skip_new_categories', 'label' => false]) ?> <span>Запретить создание новых категорий</span></label>
							</div>
							<div class="col-md-12 form-group">
								<label class="checkbox-block"><?= Html::activeCheckbox($model, 'external_images', ['name' => 'external_images', 'label' => false]) ?> <span>Использовать внешние тумбы (не будут скачиваться и нарезаться)</span></label>
							</div>

							<div class="col-md-12 form-group">
								<label class="control-label">Шаблон для ролика (по умолчанию используется view)</label>
								<?= Html::activeInput('text', $model, 'template', ['name' => 'template', 'class' => 'form-control', 'style' => 'width:200px']) ?>
                			</div>
						</div>

					</div>


					<div class="box-footer clearfix">
					    <div class="form-group">
							<?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
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
foreach ($model->getOptions() as $key => $val) {
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
		    name: 'fields[]'
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

  $('#preset').on('change', function() {
     document.forms['preset-select'].submit();
  });
JS;

$this->registerJS($js);
?>
