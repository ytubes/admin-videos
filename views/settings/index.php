<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Настройки';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">

	<div class="col-md-3">
		<?= \backend\widgets\SettingsMenu::widget(); ?>
	</div>

	<div class="col-md-9">
		<?php $form = ActiveForm::begin([
			'action' => Url::to(['index']),
		]); ?>
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#rotation" data-toggle="tab" aria-expanded="true">Ротация</a></li>
					<li class=""><a href="#related" data-toggle="tab" aria-expanded="false">Похожие ролики</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="rotation">
					    <?= $form->field($model, 'items_per_page')
					    		->textInput(['maxlength' => true])
					    		->label('Количество тумб на страницу')
					    ?>

					    <?= $form->field($model, 'pagination_buttons_count')
					    		->textInput(['maxlength' => true])
					    		->label('Количество кнопок в пагинации')
					    ?>

					    <?= $form->field($model, 'recalculate_ctr_period')
					    		->textInput(['maxlength' => true])
					    		->label('Период пересчета CTR (в показах)')
					    		->hint('Параметр рассчитывает CTR за последние N показов. Расчет производится плавно и поделен на 5 этапов.')
					    ?>

					    <?= $form->field($model, 'test_item_period')
					    		->textInput(['maxlength' => true])
					    		->label('Тестовый период тумбы (в показах)')
					    		->hint('Во время тестового периода тумба будет показываться в тестовой зоне на странице.
					    				По завершению теста тумба будет показываться на общих основаниях с учетом текущего CTR')
					    ?>

					    <?= $form->field($model, 'test_items_start')
					    		->textInput(['maxlength' => true])
					    		->label('После какой тумбы будет тестовая зона')
					    ?>

					    <?= $form->field($model, 'test_items_percent')
					    		->textInput(['maxlength' => true])
					    		->label('Процент тестовых тумб на странице')
					    		->hint('Рассчет ведется от общего числа тумб')
					    ?>

					</div>

					<div class="tab-pane" id="related">

						<div class="form-group">
							<label><?= Html::activeCheckbox($model, 'related_enable', ['label' => false]) ?> <span>Включить отображение виджета "Похожие видео"</span></label>
							<div class="help-block"></div>
						</div>

					    <?= $form->field($model, 'related_number')
					    		->textInput(['maxlength' => true])
					    		->label('Сколько похожих роликов искать')
					    ?>

						<div class="form-group">
							<label><?= Html::activeCheckbox($model, 'related_allow_description', ['label' => false]) ?> <span>Учитывать описание</span></label>
							<div class="help-block"></div>
						</div>

						<div class="form-group">
							<label><?= Html::activeCheckbox($model, 'related_allow_categories', ['label' => false]) ?> <span>Учитывать категории</span></label>
							<div class="help-block"></div>
						</div>

					</div>
				</div>

				<div class="box-footer clearfix">
				    <div class="form-group">
						<?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
					</div>
				</div>
			</div>
		<?php ActiveForm::end(); ?>

	</div>
</div>
