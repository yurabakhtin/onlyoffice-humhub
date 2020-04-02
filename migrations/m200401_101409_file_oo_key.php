<?php

use yii\db\Migration;

class m200401_101409_file_oo_key extends Migration
{
    public function up()
    {
        try {
            $this->addColumn('file', 'onlyoffice_key', $this->char(20));
        } catch (\Exception $ex) {

        }
    }

    public function down()
    {
        echo "m170613_101409_file_oo_key cannot be reverted.\n";

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
