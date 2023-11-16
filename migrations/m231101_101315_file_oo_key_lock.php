<?php

use yii\db\Migration;

/**
 * Class m231101_101315_file_oo_key_lock
 */
class m231101_101315_file_oo_key_lock extends Migration
{
    public function up()
    {
        try {
            $this->addColumn('file', 'onlyoffice_key_lock', $this->boolean()->defaultValue(false));
        } catch (\Exception $ex) {

        }
    }

    public function down()
    {
        echo "m231101_101315_file_oo_key_lock cannot be reverted.\n";

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
