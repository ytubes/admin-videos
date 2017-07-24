<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */

$this->title = 'Разное';
$this->params['breadcrumbs'][] = $this->title;

?>
<section class="content">

	<div class="row">
		<div class="col-md-12">

			<div class="box box-default">
				<div class="box-header with-border">
					<i class="fa fa-wrench"></i><h3 class="box-title">Разное</h3>
	            </div>

	            <div class="box-body pad">

					<table class="table">
						<tr>
							<td>
								<h4>Случайные даты публикации видео</h4>
								<div>Задать случайную дату в промежутке за последний год по текущую дату.</div>
							</td>
							<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-info" id="random_date" data-action="<?=Url::to(['random-date'])?>">Задать дату</button></td>
						</tr>
						<tr>
							<td>
								<h4>Обнуление статистики</h4>
								<div>Обнулить полностью статистику кликов и показов тумб, категорий. А также просмотры видео, лайки и дизлайки.</div>
							</td>
							<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-warning" id="clear_stats" data-action="<?=Url::to(['clear-stats'])?>">Обнулить</button></td>
						</tr>
						<tr>
							<td>
								<h4>Очистить базу видео</h4>
								<div>Полностью удалить видео, скриншоты, статистику по тумбам.</div>
							</td>
							<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-danger" id="clear_videos" data-action="<?=Url::to(['clear-videos'])?>">Удалить все видео</button></td>
						</tr>
					</table>

				</div>
			</div>

		</div>
	</div>
</section>

<?php

$js = <<< 'JS'
	(function() {

		$('#clear_stats').click(function(event) {
			event.preventDefault();
			var actionUrl = $(this).data('action');

			if (confirm('Обнулить статистику тумб, категорий и видео?')) {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('Статистика сброшена', 'Успех!');
					} else {
						toastr.warning('Нечего очищать', 'Внимание!');
					}
				}, 'json');
			}
		});

		$('#random_date').click(function(event) {
			event.preventDefault();
			var actionUrl = $(this).data('action');

			if (confirm('Задать случайную дату у всех видео роликов??')) {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('Новые даты публикации видео роликов заданы', 'Успех!');
					} else {
						toastr.warning('что-то пошло не так', 'Внимание!');
					}
				}, 'json');
			}
		});

		$('#clear_videos').click(function(event) {
			event.preventDefault();
			var actionUrl = $(this).data('action');
			var confirmed = prompt('Для полного удаления видео напишите слово DELETE', '');

			if (confirmed != null && confirmed === 'DELETE') {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('Ролики удалены', 'Успех!');
					} else {
						toastr.warning('Нечего удалять', 'Внимание!');
					}
				}, 'json');
			}
		});
	})();
JS;

$this->registerJS($js, \yii\web\View::POS_END);

?>
