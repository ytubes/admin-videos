<?php

use yii\db\Migration;

class m170727_112448_add_import_option1 extends Migration
{
    public function up()
    {
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
    }
}
