<?php

use yii\db\Migration;

/**
 * Class m220427_072441_mentions
 */
class m220427_072441_mentions extends Migration
{
    public function up()
    {
        try {
            $this->createTable('onlyoffice_mention', [
                'id' => $this->primaryKey(),
                'file_id' => $this->integer()->notNull(),
                'message' => $this->string(255),
                'anchor' => $this->string(255)->notNull(),
            ]);

            $this->addForeignKey('fk_file', 'onlyoffice_mention', 'file_id', 'file', 'id', 'CASCADE');
        } catch (\Exception $ex) {
        }
    }

    public function down()
    {
        echo "m220427_072441_mentions cannot be reverted.\n";

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
