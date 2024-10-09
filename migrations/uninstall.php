<?php

use yii\db\Migration;

class uninstall extends Migration
{
    public function up()
    {
        $this->dropTable('onlyoffice_share');
        $this->dropTable('onlyoffice_mention');
        $this->dropColumn('file', 'onlyoffice_key');
    }
    public function down()
    {
        echo "uninstall does not support migration down.\n";
        return false;
    }
}
