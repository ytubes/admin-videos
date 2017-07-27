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
    }

    public function down()
    {
		// ALTER TABLE `videos_import_feeds` DROP `skip_first_line`;
        $tableName = 'videos_import_feeds';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}`  DROP `skip_first_line`");
	    }
    }
}
