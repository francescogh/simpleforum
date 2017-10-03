<?php

use yii\db\Migration;

/**
 * Handles the creation of table `thread_vote`.
 */
class m170929_230619_create_thread_vote_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('thread_vote', [
            'user_id' => $this->integer()->notNull(),
        	'thread_id'	=> $this->integer()->notNull(),
        	'vote' => $this->integer()->notNull(),
        ]);
        
        $this->addPrimaryKey('thread_vote_pk', 'thread_vote', ['user_id', 'thread_id']);

        // add foreign key for table `thread`
        $this->addForeignKey(
            'fk-thread_vote-thread_id',
            'thread_vote',
            'thread_id',
            'thread',
            'id',
            'CASCADE'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-thread_vote-user_id',
            'thread_vote',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('thread_vote');
    }
}
