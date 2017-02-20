<?php

use yii\db\Migration;

class m130524_201444_add_column_lang_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}','lang','string');
    }

    public function down()
    {
        echo "m130524_201444_add_column_lang_to_user\n";

        return false;
        //$this->dropColumn('{{%user}}','lang');
    }
}
