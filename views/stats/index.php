<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'Статистика';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['subtitle'];

$progress = ($data['total_rows'] > 0) ? round(($data['tested_rows'] / $data['total_rows'] * 100), 2) : 0;

?>

<div class="row">
	<div class="col-md-12">

		<div class="box box-primary">
			<div class="box-header with-border">
				<i class="fa fa-bar-chart"></i><h3 class="box-title">Статистика ротации</h3>
            </div>

            <div class="box-body pad">
            	Всего тумб: <b><?= $data['total_rows'] ?></b><br>
            	Тестирумые тумбы: <b><?= $data['test_rows'] ?></b><br>
            	Завершившие тест: <b><?= $data['tested_rows'] ?></b><br>
            	Нулевой цтр у прошедших тест: <b><?= $data['tested_zero_ctr'] ?></b><br><br>
				<div class="progress-group">
					<span class="progress-text">Прогресс тестирования</span>
					<span class="progress-number"><b><?= $data['tested_rows'] ?></b>/<?= $data['total_rows'] ?> (<?= $progress ?>%)</span>

					<div class="progress">
						<div class="progress-bar progress-bar-aqua" style="width: <?= $progress ?>%"></div>
					</div>
				</div>

			</div>


			<div style="padding: 0 15px;">
				<div class="box box-success collapsed-box">
					<div class="box-header with-border">
						<h3 class="box-title">По категориям</h3>
						<div class="box-tools pull-right">
				            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				    <!-- /.box-header -->
					<div class="box-body" style="display: none;">
		            	Всего категорий: <b><?= $data['total_categories'] ?></b><br>

						<?php if (!empty($data['categories'])): ?>
							<?php foreach($data['categories'] as $category_item): ?>
								<div class="progress-group">
									<?php $progress = ($category_item['total_rows'] > 0) ? round(($category_item['tested_rows'] / $category_item['total_rows'] * 100), 2) : 0; ?>
									<span class="progress-text"><?= $category_item['title'] ?></span>
									<span class="progress-number"><b><?= $category_item['tested_rows'] ?></b>/<?= $category_item['total_rows'] ?> (<?= $progress ?>%)</span>

									<div class="progress">
										<div class="progress-bar <?= ($progress == 100) ? 'progress-bar-green' : 'progress-bar-yellow' ?>" style="width: <?= $progress ?>%"></div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				    <!-- /.box-body -->
				</div>
			</div>


			<div class="box-footer clearfix">
			    <div class="form-group">
					<?= Html::a('<i class="fa fa-fw fa-refresh"></i> Обновить', ['/videos/stats/index'], ['class' => 'btn btn-primary']) ?>
				</div>
			</div>


		</div>

	</div>
</div>
