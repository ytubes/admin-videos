<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Импорт';
$this->params['subtitle'] = 'Категории видео';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Категории видео';

?>

<div class="row">
	<div class="col-md-12">

	<?php if ($model->hasNotInsertedRows()): ?>
		<?php $numNotInsertedRows = count($model->getNotInsertedRows()) ?>
		<div class="box box-danger collapsed-box">
			<div class="box-header with-border">
				<h3 class="box-title">Не вставленные строки</h3>

				<div class="box-tools pull-right">
					<span data-toggle="tooltip" title="" class="badge bg-red" data-original-title="<?= $numNotInsertedRows ?> строки"><?= $numNotInsertedRows ?></span>
						<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
		                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
		        </div>
			</div>
		            <!-- /.box-header -->
			<div class="box-body" style="display: none;">
				<div class="row">
					<div class="col-md-12 form-group">
						<?= Html::textarea('csv_not_inserted_rows', implode(PHP_EOL, $model->getNotInsertedRows()), [
							'id' => 'csv-not-inserted-rows',
							'class' => 'form-control csv-not-inserted-rows',
							'rows' => 12]
						) ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

		<div class="box box-primary">
			<div class="box-header with-border">
				<i class="glyphicon glyphicon-import text-light-violet"></i><h3 class="box-title">Импорт категорий</h3>
            </div>

			<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

	            <div class="box-body pad">

					<?php if ($model->hasErrors('csv_rows')): ?>
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<h4><i class="icon fa fa-exclamation-circle"></i> Следующие записи не были добавлены: </h4>
							<ul>
							<?php foreach ($model->getErrors('csv_rows') as $errorMessage): ?>
								<?php if (is_array($errorMessage)): ?>
									<?php $key = key($errorMessage); ?>
									<li>Категория: <b><?= Html::encode($key) ?></b>:</li>
									<ul>
										<?php foreach ($errorMessage[$key] as $error): ?>
											<li><?= $error ?></li>
										<?php endforeach ?>
									</ul>
								<?php else: ?>
									<li><?= $errorMessage ?></li>
								<?php endif ?>
							<?php endforeach ?>
							</ul>
						</div>
					<?php endif; ?>

					<h3>Настройки ввода</h3>
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

					<h4>Поля csv (название или ID обязательны)</h4>
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

							<p class="help-block">Убедитесь в соответствии полей файла c текущими настройками.</p>
						</div>

						<div class="col-md-12 form-group">
							<label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_first_line', ['name' => 'skip_first_line', 'label' => false]) ?> <span>Пропустить первую строчку</span></label>
							<div class="help-block">Активировать, если в первой строке указаны названия столбцов</div>
						</div>
						<div class="col-md-12 form-group">
							<label class="checkbox-block"><?= Html::activeCheckbox($model, 'update_category', ['name' => 'update_category', 'label' => false]) ?> <span>Обновить при совпадении id или названия</span></label>
							<div class="help-block">Если опция не активна, при совпадении идентификатора или названия импортируемая категория будет игнорироваться.</div>
						</div>
					</div>

				</div>


				<div class="box-footer clearfix">
				    <div class="form-group">
						<?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
						<?= Html::a('К категориям', ['categories/index'], ['class' => 'btn btn-warning']) ?>
					</div>
				</div>

			<?php ActiveForm::end(); ?>

		</div>

	</div>
</div>

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
JS;

$this->registerJS($js);
?>
