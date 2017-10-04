<?php

use yii\db\Migration;

class m171004_101411_category_enabled extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = 'videos_categories';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

		if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}` ADD `enabled` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `reset_clicks_period`");
	    	$this->execute("UPDATE `{$tableName}` SET `enbled`=1");
	    }
    }

    public function down()
    {
        $tableName = 'videos_categories';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema !== null) {
	    	$this->execute("ALTER TABLE `{$tableName}`  DROP `enabled`");
	    }
    }
}
