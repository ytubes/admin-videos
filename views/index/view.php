<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\Videos */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'Videos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="videos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('videos', 'Update'), ['update', 'id' => $model->video_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('videos', 'Delete'), ['delete', 'id' => $model->video_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('videos', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'video_id',
            'image_id',
            'user_id',
            'slug',
            'title',
            'description:ntext',
            'short_description',
            'orientation',
            'duration',
            'video_url:url',
            'embed:ntext',
            'on_index',
            'likes',
            'dislikes',
            'comments_num',
            'views',
            'status',
            'published_at',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
