<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\Videos */

$this->title = Yii::t('videos', 'Update {modelClass}: ', [
    'modelClass' => 'Videos',
]) . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'Videos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->video_id]];
$this->params['breadcrumbs'][] = Yii::t('videos', 'Update');
?>
<div class="videos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
