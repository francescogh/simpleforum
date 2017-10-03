<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_vote`.
 */
class m170929_233028_create_post_vote_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_vote', [
            'user_id' => $this->integer()->notNull(),
        	'post_id'	=> $this->integer()->notNull(),
        	'vote' => $this->integer()->notNull(),
        ]);
        
        $this->addPrimaryKey('postvote_pk', 'post_vote', ['user_id', 'post_id']);

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_vote-post_id',
            'post_vote',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post_vote-user_id',
            'post_vote',
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
        $this->dropTable('post_vote');
    }
}
