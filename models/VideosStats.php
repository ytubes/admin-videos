<?php

namespace backend\modules\videos\models;

use Yii;

/**
 * This is the model class for table "videos_stats".
 *
 * @property integer $category_id
 * @property integer $image_id
 * @property integer $video_id
 * @property integer $best_image
 * @property string $published_at
 * @property integer $duration
 * @property integer $shows
 * @property integer $clicks
 * @property double $ctr
 *
 * @property Videos $video
 * @property VideosCategories $category
 * @property VideosImages $image
 */
class VideosStats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_stats';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'image_id', 'video_id'], 'required'],
            [['category_id', 'image_id', 'video_id', 'best_image', 'duration', 'current_shows', 'current_clicks'], 'integer'],
            [['published_at'], 'safe'],
            [['ctr'], 'number'],
            [['video_id'], 'exist', 'skipOnError' => true, 'targetClass' => Videos::className(), 'targetAttribute' => ['video_id' => 'video_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideosCategories::className(), 'targetAttribute' => ['category_id' => 'category_id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideosImages::className(), 'targetAttribute' => ['image_id' => 'image_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'Category ID',
            'image_id' => 'Image ID',
            'video_id' => 'Video ID',
            'best_image' => 'Best Image',
            'published_at' => 'Published At',
            'duration' => 'Duration',
            'total_shows' => 'Shows',
            'total_clicks' => 'Clicks',
            'ctr' => 'Ctr',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Videos::className(), ['video_id' => 'video_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(VideosCategories::className(), ['category_id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(VideosCategories::className(), ['category_id' => 'category_id'])
				->viaTable(VideosStats::tableName(), ['video_id' => 'video_id'], function ($query) {
			        /* @var $query \yii\db\ActiveQuery */

			    	$query->select(['video_id', 'category_id']);
		        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(VideosImages::className(), ['image_id' => 'image_id']);
    }
}
