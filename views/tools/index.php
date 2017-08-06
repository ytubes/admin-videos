<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */

$this->title = 'Разное';
$this->params['subtitle'] = 'Видео';
$this->params['breadcrumbs'][] = $this->title;

?>

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
							<h4>Пересчитать видео в категориях</h4>
							<div class="text-muted">В категориях будет произведен подсчет только активных видео.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-info" id="recalculate_categories_videos" data-action="<?=Url::to(['recalculate-categories-videos'])?>">Пересчитать видео</button></td>
					</tr>
					<tr>
						<td>
							<h4>Установить тумбы для категорий</h4>
							<div class="text-muted">Тумбы установятся от первых видео на странице категории</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-success" id="set_categories_thumbs" data-action="<?=Url::to(['set-categories-thumbs'])?>">Установить тумбы</button></td>
					</tr>
					<tr>
						<td>
							<h4>Случайные даты публикации видео</h4>
							<div class="text-muted">Задать случайную дату в промежутке за последний год по текущую дату.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-info" id="random_date" data-action="<?=Url::to(['random-date'])?>">Задать дату</button></td>
					</tr>
					<tr>
						<td>
							<h4>Обнуление статистики</h4>
							<div class="text-muted">Обнулить полностью статистику кликов и показов тумб, категорий. А также просмотры видео, лайки и дизлайки.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-warning" id="clear_stats" data-action="<?=Url::to(['clear-stats'])?>">Обнулить статистику</button></td>
					</tr>
					<tr>
						<td>
							<h4>Перегенерировать "похожие" видео</h4>
							<div class="text-red">Внимание! Данная функция запускает очень медленный процесс.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-success" id="regenerate-related" data-action="<?=Url::to(['regenerate-related'])?>">Запустить генерацию</button></td>
					</tr>
					<tr>
						<td>
							<h4>Очистить "похожие" видео</h4>
							<div class="text-muted">"Похожие" ролики будут полностью удалены из базы.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-warning" id="clear_related" data-action="<?=Url::to(['clear-related'])?>">Очистить "похожие"</button></td>
					</tr>
					<tr>
						<td>
							<h4>Очистить базу видео</h4>
							<div class="text-muted">Полностью удалить видео, скриншоты, статистику по тумбам.</div>
						</td>
						<td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-danger" id="clear_videos" data-action="<?=Url::to(['clear-videos'])?>">Удалить все видео</button></td>
					</tr>
				</table>

			</div>
		</div>

	</div>
</div>

<?php

$js = <<< 'JS'
	(function() {
		$('#recalculate_categories_videos').click(function(event) {
			event.preventDefault();
			var actionUrl = $(this).data('action');
			var bttn = $(this);

			bttn.prop('disabled', true);

			$.post(actionUrl, function( data ) {
				if (data.success == true) {
					toastr.success('Счетчик видео в категориях обновлен', 'Успех!');
				} else {
					toastr.warning('Нечего обновлять', 'Внимание!');
				}
			}, 'json')
			.done(function() {
			    bttn.prop('disabled', false);
			});
		});

		$('#set_categories_thumbs').click(function(event) {
			event.preventDefault();
			var bttn = $(this);
			var actionUrl = $(this).data('action');

			bttn.prop('disabled', true);

			$.post(actionUrl, function( data ) {
				if (data.success == true) {
					toastr.success('Тумбы установлены', 'Успех!');
				} else {
					toastr.warning('Установка тумб для категорий не была произведена', 'Внимание!');
				}
			}, 'json')
			.done(function() {
			    bttn.prop('disabled', false);
			});
		});

		$('#regenerate-related').click(function(event) {
			event.preventDefault();
			var bttn = $(this);
			var actionUrl = $(this).data('action');

			bttn.prop('disabled', true);

			if (confirm('Запустить генерацию "похожих" видео (очень медленно)?')) {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('"Похожие" видео сгенерированы.', 'Успех!');
					} else {
						toastr.warning('что-то пошло не так', 'Внимание!');
					}
				}, 'json')
				.done(function() {
				    bttn.prop('disabled', false);
				});
			}
		});

		$('#random_date').click(function(event) {
			event.preventDefault();
			var bttn = $(this);
			var actionUrl = $(this).data('action');

			bttn.prop('disabled', true);

			if (confirm('Задать случайную дату у всех видео роликов??')) {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('Новые даты публикации видео роликов заданы', 'Успех!');
					} else {
						toastr.warning('что-то пошло не так', 'Внимание!');
					}
				}, 'json')
				.done(function() {
				    bttn.prop('disabled', false);
				});
			}
		});

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

		$('#clear_related').click(function(event) {
			event.preventDefault();
			var actionUrl = $(this).data('action');

			if (confirm('Очистить "похожие видео"?')) {
				$.post(actionUrl, function( data ) {
					if (data.success == true) {
						toastr.success('"Похожие видео" очищены', 'Успех!');
					} else {
						toastr.warning('Нечего очищать', 'Внимание!');
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
