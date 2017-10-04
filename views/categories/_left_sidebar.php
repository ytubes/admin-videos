<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>

<div class="box box-default">
	<div class="box-header with-border">
		<i class="fa fa-list"></i><h3 class="box-title">Категории</h3>
		<div class="box-tools pull-right">
			<div class="btn-group">
				Сортировка: <?= Html::dropDownList('sort_items', 'position', [
					'key' => 'ID',
					'position' => 'Ручная',
					'title' => 'Название',
					'ctr' => 'CTR',
				], [
					'id' => 'sort-items',
					'class' => 'btn-default btn-sm',
				]) ?>
			</div>
		</div>
    </div>

    <div class="box-body pad">
    	<?php if (!empty($categories)): ?>
    		<ul id="sortable" class="categories-list">
    		<?php foreach ($categories as $category): ?>

				<li class="categories-list__item <?= ($category->category_id === $active_id)? 'active' : ''?> <?= (!$category->isActive()) ? 'bg-pink--horizontal-gradient' : '' ?>" data-key="<?= $category->category_id ?>" data-position="<?= $category->position ?>" data-title="<?= $category->title ?>" data-ctr="<?= $category->ctr ?>">
					<span class="categories-list__span categories-list__span--id"><?= $category->category_id ?>: </span><?= Html::a($category->title, ['update', 'id' => $category->category_id], ['title' => 'Редактирование', 'class' => 'categories-list__a categories-list__a--title']) ?>
					<ul class="categories-list__actions action-buttons pull-right">
						<?php $icon = ($category->isActive()) ? '<span class="glyphicon glyphicon-eye-open"></span>' : '<span class="glyphicon glyphicon-eye-close text-red"></span>' ?>
						<li class="action-buttons__item">
							<?= Html::a(
								$icon,
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
				<?= Html::a('<i class="glyphicon glyphicon-export" style="color:#ff196a"></i> Экспорт категорий', ['export'], ['class' => 'btn btn-default', 'title' => 'Экспорт категорий']) ?>
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

$('#sort-items').on('change', function () {
	$("#sortable .categories-list__item").sort(sort_li).appendTo('#sortable');

	function sort_li(a, b) {
		return ($(b).data($('#sort-items').val())) < ($(a).data($('#sort-items').val())) ? 1 : -1;
	}
});
JAVASCRIPT;

$this->registerJS($script);
