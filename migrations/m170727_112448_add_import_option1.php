<?php

use yii\db\Migration;

class m170727_112448_add_import_option1 extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        //ALTER TABLE `videos_import_feeds` ADD `skip_first_line` TINYINT(1) NOT NULL DEFAULT '1' AFTER `fields`;
        $tableName = 'videos_import_feeds';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` ADD `skip_first_line` TINYINT(1) NOT NULL DEFAULT '1' AFTER `fields`");
	    }

        $tableName = 'videos_categories';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

		if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` CHANGE `items_count` `videos_num` INT UNSIGNED NOT NULL DEFAULT '0'");
	    }

        $tableName = 'videos';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

		if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` CHANGE `comments_count` `comments_num` SMALLINT UNSIGNED NOT NULL DEFAULT '0'");
	    }


        $tableName = 'videos_categories_map';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
            $this->createTable($tableName, [
                'category_id' => 'smallint(5) unsigned NOT NULL',
                'video_id' => 'int(10) UNSIGNED NOT NULL DEFAULT 0',
            ], $tableOptions);

            $this->addPrimaryKey('category_id', $tableName, ['category_id', 'video_id']);
            $this->createIndex('video_id', $tableName, 'video_id');

                // add foreign key for table `videos_categories_map`
            $this->addForeignKey(
                'videos_categories_map_ibfk_1',
                $tableName,
                'category_id',
                'videos_categories',
                'category_id',
                'CASCADE',
                'CASCADE'
            );

            $this->addForeignKey(
                'videos_categories_map_ibfk_2',
                $tableName,
                'video_id',
                'videos',
                'video_id',
                'CASCADE',
                'CASCADE'
            );

            $this->execute("
            	INSERT INTO `{$tableName}` (`category_id`, `video_id`)
				SELECT `category_id`, `video_id`
				FROM `videos_stats`
            ");
        }
    }

    public function down()
    {
		// ALTER TABLE `videos_import_feeds` DROP `skip_first_line`;
        $tableName = 'videos_import_feeds';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}`  DROP `skip_first_line`");
	    }

        $tableName = 'videos_categories';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);
		if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` CHANGE `videos_num` `items_count` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'");
	    }

        $tableName = 'videos';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);
		if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` CHANGE `comments_num` `comments_count` SMALLINT UNSIGNED NOT NULL DEFAULT '0'");
	    }

	    $tableName = 'videos_categories_map';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);
		if ($tableSchema !== null) {
	    	$this->dropTable($tableName);
	    }
    }
}
