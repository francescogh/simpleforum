<?php

use yii\db\Migration;

/**
 * Handles the creation of table `thread`.
 */
class m170929_223045_create_thread_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('thread', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
        	'creationDate' => $this->dateTime()->notNull(),
            'author_id' => $this->integer()->notNull(),
        	'score' => $this->integer()->notNull()->defaultValue(0),
        	'views' => $this->integer()->notNull()->defaultValue(0),
        ]);
        
        // creates index for column `author_id`
        $this->createIndex(
            'idx-thread-author_id',
            'thread',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-thread-author_id',
            'thread',
            'author_id',
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
        $this->dropTable('thread');
    }
}
