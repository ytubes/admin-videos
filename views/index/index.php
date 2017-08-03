<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('videos', 'Videos');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="videos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('videos', 'Create Videos'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'video_id',
            //'image_id',
            //'user_id',
            //'slug',
            'title',
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
            'published_at',
            // 'created_at',
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
