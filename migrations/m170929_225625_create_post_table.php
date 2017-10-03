<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post`.
 */
class m170929_225625_create_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'content' => $this->text()->notNull(),
        	'creationDate' => $this->dateTime()->notNull(),
            'author_id' => $this->integer()->notNull(),
        	'thread_id'	=> $this->integer()->notNull(),
        	'score'	=> $this->integer()->notNull()->defaultValue(0),
        ]);
        
        // creates index for column `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );
        
        // creates index for column `thread_id`
        $this->createIndex(
            'idx-post-thread_id',
            'post',
            'thread_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        // add foreign key for table `thread`
        $this->addForeignKey(
            'fk-post-thread_id',
            'post',
            'thread_id',
            'thread',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
