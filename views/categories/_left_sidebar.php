<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>

<div class="box box-default">
	<div class="box-header with-border">
		<i class="fa fa-list"></i><h3 class="box-title">Категории</h3>
		<div class="box-tools pull-right">
			<div class="btn-group">
				<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i>', ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
				<?= Html::a('<i class="glyphicon glyphicon-export" style="color:#ff196a"></i>', ['export'], ['class' => 'btn btn-default btn-sm', 'title' => 'Экспорт категорий']) ?>
			</div>
		</div>
    </div>

    <div class="box-body pad">
    	<?php if (!empty($categories)): ?>
    		<ul id="sortable" class="categories-list">
    		<?php foreach ($categories as $category): ?>

				<li class="categories-list__item <?= ($category->category_id === $active_id)? 'active' : ''?>" data-key="<?= $category->category_id ?>">
					<span class="categories-list__span categories-list__span--id"><?= $category->category_id ?>: </span><?= Html::a($category->title, ['update', 'id' => $category->category_id], ['title' => 'Редактирование', 'class' => 'categories-list__a categories-list__a--title']) ?>
					<ul class="categories-list__actions action-buttons pull-right">
						<li class="action-buttons__item">
							<?= Html::a(
								'<span class="glyphicon glyphicon-eye-open"></span>',
								['view', 'id' => $category->category_id],
								[
									'title' => 'Просмотр информации',
									'class' => 'action-buttons__a',
								]
							) ?>
						</li>
						<li class="action-buttons__item">
							<?= Html::a(
								'<span class="glyphicon glyphicon-trash text-red"></span>',
								['delete', 'id' => $category->category_id],
								[
									'title' => 'Удалить',
									'class' => 'action-buttons__a',
									'aria-label' => 'Удалить',
									'data-confirm' => 'Вы уверены, что хотите удалить эту категорию?',
									'data-method' => 'post',
								]
							) ?>
						</li>
					</ul>
				</li>

			<?php endforeach ?>
			</ul>
		<?php else: ?>
			Нет категорий
		<?php endif ?>
	</div>

	<div class="box-footer clearfix">
	    <div class="form-group">
			<?= Html::submitButton('<span class="glyphicon glyphicon-save"></span> Сохранить порядок сортировки',
				[
					'id' => 'save-order',
					'class' => 'btn btn-primary',
					'data-save' => Url::to(['save-order'])
				]
			) ?>
		</div>
	</div>
</div>

<?php

$script = <<< 'JAVASCRIPT'
$("#sortable").sortable({
  placeholder: 'categories-list__placeholder',
  cursor: 'move',
});

$('#save-order').on('click', function () {
	var requestUrl = $(this).data('save');
    var order = $('#sortable').sortable('serialize', {
        attribute: 'data-key',
        key: 'order[]',
        expression: /(.+)/
    });

	var request = $.ajax({
		url: requestUrl,
		method: 'POST',
		data: order,
		dataType: "json"
	});

	request.done(function(response) {
		if (response.status === 'success') {
			toastr.success(response.message);
		} else if (response.status === 'error') {
			toastr.error(response.message);
		}
	});

	request.fail(function( jqXHR, textStatus ) {
		toastr.warning('Request failed: ' + textStatus);
	});
});
JAVASCRIPT;

$this->registerJS($script);
