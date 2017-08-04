<?php
use yii\helpers\Url;
use yii\helpers\Html;
use ytubes\videos\models\VideoStatus;

$actionUrl = Url::to(['mass-actions/change-status']);

?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title" id="modal-title">Установка статуса</h4>
</div>

<div class="modal-body">
	<div class="row">
		<div class="col-md-5 form-group field-selectuserform-per_page">
			<?= Html::dropDownList('status', [], $listStatus, ['id' => 'select-status', 'class' => 'form-control'])?>
		</div>
	</div>

<div class="modal-footer" id="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
	<button type="button" class="btn btn-primary" id="change-status" data-url="<?= $actionUrl ?>">Установить</button>
</div>

<script>
(function() {
	$('#change-status').click(function(event) {
		event.preventDefault();
		var actionUrl = $(this).data('url');
		var statusId = $('#select-status').val();
		var keys = $('#list-videos').yiiGridView('getSelectedRows');

		if (keys.length == 0) {
			alert('Нужно выбрать хотябы 1 элемент');
			return;
		}

		$.post(actionUrl, { 'videos_ids[]':keys, 'status':statusId }, function( data ) {
			if (data.status === 'success') {
				window.location.reload();
			} else if (data.status === 'error') {
				toastr.error('Ошибка какая-то');
			}
		}, 'json');
	});
})();
</script>