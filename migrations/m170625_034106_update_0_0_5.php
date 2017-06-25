<?php

use yii\db\Schema;
use yii\db\Migration;

class m170625_034106_update_0_0_5 extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tableName = 'videos_import_feeds';
        $tableSchema = \Yii::$app->db->schema->getTableSchema($tableName);

        if ($tableSchema === null) {
	        $this->createTable($tableName, [
	            'feed_id' => 'smallint(5) unsigned NOT NULL',
	            'name' => 'varchar(255) NOT NULL DEFAULT \'\'',
	            'description' => 'varchar(255) NOT NULL DEFAULT \'\'',
	            'delimiter' => 'varchar(16) NOT NULL DEFAULT \'|\'',
	            'enclosure' => 'varchar(16) NOT NULL DEFAULT \'"\'',
	            'fields' => 'text NULL DEFAULT NULL',
	            'skip_duplicate_urls' => 'tinyint(1) unsigned NOT NULL DEFAULT 1',
	            'skip_duplicate_embeds' => 'tinyint(1) unsigned NOT NULL DEFAULT 1',
	            'skip_new_categories' => 'tinyint(1) unsigned NOT NULL DEFAULT 1',
	            'external_images' => 'tinyint(1) unsigned NOT NULL DEFAULT 1',
				'template' => 'varchar(64) NOT NULL DEFAULT \'\'',
	        ], $tableOptions);

	        $this->addPrimaryKey('feed_id', $tableName, 'feed_id');
	        $this->execute("ALTER TABLE `{$tableName}` MODIFY `feed_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT");
		}

			//ALTER TABLE `videos` ADD `template` VARCHAR(255) NOT NULL DEFAULT '' AFTER `views`;
		$this->addColumn('videos', 'template', 'VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `views`');
    }

    public function down()
    {
        $this->dropTable('videos_import_feeds');
        $this->dropColumn('videos', 'template');

        return true;
    }
}
