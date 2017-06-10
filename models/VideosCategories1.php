<?php

namespace backend\modules\videos\models;

use Yii;

/**
 * This is the model class for table "videos_categories".
 *
 * @property integer $category_id
 * @property integer $position
 * @property string $slug
 * @property string $image
 * @property string $meta_title
 * @property string $meta_description
 * @property string $title
 * @property string $fp_title
 * @property string $op_title
 * @property string $fp_descr
 * @property string $op_descr
 * @property integer $items_count
 * @property integer $on_index
 * @property integer $shows
 * @property integer $clicks
 * @property double $ctr
 * @property integer $reset_clicks_period
 * @property string $created_at
 * @property string $updated_at
 *
 * @property VideosCategoriesMap[] $videosCategoriesMaps
 * @property Videos[] $videos
 * @property VideosStats[] $videosStats
 */
class VideosCategories extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['position', 'items_count', 'on_index', 'shows', 'clicks', 'reset_clicks_period'], 'integer'],
            [['description', 'seotext', 'param1', 'param2', 'param3'], 'string'],
            [['ctr'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug', 'image', 'meta_title', 'meta_description', 'title', 'h1'], 'string', 'max' => 255],
            [['slug'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'Category ID',
            'position' => 'Position',
            'slug' => 'Slug',
            'image' => 'Image',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'title' => 'Title',
            'h1' => 'H1 Title',
            'description' => 'Description',
            'seotext' => 'seotext',
            'items_count' => 'Items Count',
            'on_index' => 'On Index',
            'shows' => 'Shows',
            'clicks' => 'Clicks',
            'ctr' => 'Ctr',
            'reset_clicks_period' => 'Reset Clicks Period',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    /*public function getVideosCategoriesMap()
    {
        return $this->hasMany(VideosCategoriesMap::className(), ['category_id' => 'category_id']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
   /*public function getVideos()
    {
        return $this->hasMany(Videos::className(), ['video_id' => 'video_id'])->viaTable('videos_categories_map', ['category_id' => 'category_id']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideosStats()
    {
        return $this->hasMany(VideosStats::className(), ['category_id' => 'category_id']);
    }
}
