<?php

use yii\db\Migration;

class m200401_164457_shareLinks extends Migration
{

    public function up()
    {
        $this->createTable('onlyoffice_share', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'mode' => $this->string(10)->notNull(),
            'secret' => $this->string(255),
        ]);

        $this->addForeignKey('fk_file', 'onlyoffice_share', 'file_id', 'file', 'id', 'CASCADE');
        $this->createIndex('fk_onlyoffice_share_unq', 'onlyoffice_share', ['file_id', 'mode'], true);
    }

    public function down()
    {
        echo "m170308_164455_shareLinks cannot be reverted.\n";

        return false;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
