<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m170929_211607_create_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string(16)->notNull()->unique(),
            'password' => $this->string(16)->notNull(),
        	'authKey' => $this->string(64)->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('user');
    }
}
