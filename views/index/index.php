<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$dataProvider->prepare(true);
$page = $dataProvider->getPagination()->getPage() + 1;
$pageTitleSuffix = ($page > 1) ? Yii::t('app', 'page_suffix', ['page' => $page]) : '';

$this->title = 'Видео';
$this->params['subtitle'] = 'Список';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
	<div class="col-md-12">

	    <?= $this->render('_filter', [
	        'model' => $model,
	    ]) ?>

		<div class="box box-default">
			<div class="box-header with-border">
				<i class="fa fa-list"></i><h3 class="box-title">Видео ролики <?= $pageTitleSuffix ?></h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="glyphicon glyphicon-import text-light-violet"></i>', ['import/videos'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт видео']) ?>
					</div>
				</div>
            </div>

            <div class="box-body pad">

				<div class="table-actions-bar">
					<div class="btn-group">
						<button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Действия с выбранными <span class="caret"></span> </button>
			            <ul class="dropdown-menu" role="menu">
			                <li><a href="<?= Url::to(['mass-actions/change-status']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Изменить статус</a></li>
			                <li><a href="<?= Url::to(['mass-actions/change-user']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Изменить автора</a></li>
			                <li><a href="<?= Url::to(['mass-actions/set-publish-date']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Задать дату публикации <span class="text-red">(не работает)</span></a></li>
			                <li class="divider"></li>
							<li><a href="#" class="text-red" tabindex="-1" id="mass_delete_videos" data-url="<?= Url::to(['mass-actions/delete-videos']) ?>">Удалить</a></li>
						</ul>
			        </div>

					<?= LinkPager::widget([
					    'pagination' => $dataProvider->pagination,
				    	'lastPageLabel' => '>>',
				    	'firstPageLabel' => '<<',
				    	'maxButtonCount' => 7,
					    'options' => [
					    	'class' => 'pagination pagination-sm no-margin pull-right',
					    ],
					]) ?>
				</div>

			    <?= GridView::widget([
			        'dataProvider' => $dataProvider,
			        'layout'=>"{summary}\n{items}",
			        'id' => 'list-videos',
			        'options' => [
			        	'class' => 'grid-view table-responsive',
			        ],
			        'columns' => [
			        	[
			        		'class' => 'yii\grid\CheckboxColumn',
			        		'options' => [
			        			'style' => 'width:30px',
			        		],
			        	],
			            [
			            	'attribute' => 'video_id',
			            	'label' => Yii::t('app', 'id'),
			            	'value' => function ($data) {
			            		return $data->video_id;
			            	},
			        		'options' => [
			        			'style' => 'width:70px',
			        		],
			            ],
			            //'image_id',
			            //'user_id',
			            //'slug',
			            [
			            	'attribute' => 'title',
			            	'label' => Yii::t('app', 'title'),
			            	'value' => function ($data) {
			            		return $data->title;
			            	},
			            	'format' => 'html',
			            ],
			            // 'description:ntext',
			            // 'short_description',
			            // 'orientation',
			            // 'duration',
			            // 'video_url:url',
			            // 'embed:ntext',
			            // 'on_index',
			            // 'likes',
			            // 'dislikes',
			            // 'comments_num',
			            // 'views',
			            // 'status',
			            [
			            	'attribute' => 'published_at',
			            	'label' => Yii::t('app', 'published_at'),
			            	//'datetimeFormat' => 'php:d M, Y H:i',
			            	'value' => function ($data) {
			            		return Yii::$app->formatter->asDateTime($data->published_at);
			            	},
			        		'options' => [
			        			'style' => 'width:145px',
			        		],
			            ],
			            // 'created_at',
			            // 'updated_at',

			            ['class' => 'yii\grid\ActionColumn'],
			        ],
			    ]); ?>

				<div class="table-actions-bar">
					<div class="btn-group dropup">
						<button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Действия с выбранными <span class="caret"></span> </button>
			            <ul class="dropdown-menu" role="menu">
			                <li><a href="<?= Url::to(['mass-actions/change-status']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Изменить статус</a></li>
			                <li><a href="<?= Url::to(['mass-actions/change-user']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Изменить автора</a></li>
			                <li><a href="<?= Url::to(['mass-actions/set-publish-date']) ?>" tabindex="-1" data-toggle="modal" data-target="#mass_actions_modal">Задать дату публикации <span class="text-red">(не работает)</span></a></li>
			                <li class="divider"></li>
							<li><a href="#" class="text-red" tabindex="-1" id="mass_delete_videos" data-url="<?= Url::to(['mass-actions/delete-videos']) ?>">Удалить</a></li>
						</ul>
			        </div>

					<?= LinkPager::widget([
					    'pagination' => $dataProvider->pagination,
				    	'lastPageLabel' => '>>',
				    	'firstPageLabel' => '<<',
				    	'maxButtonCount' => 7,
					    'options' => [
					    	'class' => 'pagination pagination-sm no-margin pull-right',
					    ],
					]) ?>
				</div>

			</div>

		</div>

	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="mass_actions_modal" tabindex="-1" role="dialog" aria-labelledby="mass_actions_title">
<div class="modal-dialog" role="document">
	<div class="modal-content">
	</div>
</div>
</div>

<?php

$js = <<< 'JAVASCRIPT'
(function() {
	$('#mass_delete_videos').click(function(event) {
		event.preventDefault();
		var actionUrl = $(this).data('url');
		var keys = $('#list-videos').yiiGridView('getSelectedRows');

		if (keys.length == 0) {
			alert('Нужно выбрать хотябы 1 элемент');
			return;
		}

		if (confirm('Уверены, что хотите удалить выбранные видео?')) {
			$.post(actionUrl, { 'videos_ids[]':keys }, function( data ) {
				if (data.status === 'success') {
					window.location.reload();
				} else {
					toastr.error('Ошибка удаления');
				}
			}, 'json');
		}
	});

	$(document).on('hidden.bs.modal', function (e) {
	    var target = $(e.target);
	    target.removeData('bs.modal')
	    .find('.modal-content').html('');
	});
})();
JAVASCRIPT;

$this->registerJS($js, \yii\web\View::POS_END);
?>
