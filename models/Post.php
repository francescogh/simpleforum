<?php

namespace app\models;

use Yii;
use app\components\BadPatternsValidator;

/**
 * This is the model class for table "post".
 *
 * @property integer $id
 * @property string $content
 * @property string $creationDate
 * @property integer $author_id
 * @property integer $thread_id
 * @property integer $score
 *
 * @property User $author
 * @property Thread $thread
 */
class Post extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
    	$urlPattern = '/(?:^|\s)(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#]|\s)/i';
    	    	
        return [
            [['content', 'thread_id'], 'required'],
            [['content'], 'string', 'max' => 100],            
            [['content'], 'match', 'pattern' => $urlPattern, 'not' => true], // content can't contain urls
            [['content'], BadPatternsValidator::className()],
            [['thread_id'], 'integer'],
            [['thread_id'], 'exist', 'skipOnError' => true, 'targetClass' => Thread::className(), 'targetAttribute' => ['thread_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'creationDate' => 'Creation Date',
            'author_id' => 'Author ID',
            'thread_id' => 'Thread ID',
            'score' => 'Score',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
    
    /**
     * get all couples (post_id, vote) about a specific thread and specific user
     * foreach vote returning false if not found any vote, else returning the vote (-1 or 1) 
     * 
     * @param $threadId integer
     * @param $userId integer 
     * @return array
     */
    public static function getUserPostVotes($threadId, $userId)
    {
    	$rows = (new \yii\db\Query())
    		->select(['post.id', 'vote'])
    		->from('post')
    		->join('LEFT JOIN', 'post_vote', 'post_id = post.id AND user_id = '. $userId)
    		->where(['thread_id' => $threadId])
    		->all();
    			
    	$userPostVoteRows = array();
    	
    	// $userPostVoteRows is going to be indexed by real post ids and contain values in {false, 1, -1}    	
    	foreach($rows as $key => $value)
    	{
    		$userPostVoteRows[$value['id']] = ($value['vote'] === null) ? false : intval($value['vote']);
    	}
    			
    	return $userPostVoteRows;
    }
    
    /**
     * get a post vote by an user
     * returning false if not found any vote, else return the vote (-1 or 1) 
     * 
     * @param $postId integer
     * @param $userId integer 
     * @return mixed
     */
    public static function getUserPostVote($postId, $userId)
    {
    	$userPostVoteRow = (new \yii\db\Query())
    			->select('vote')
    			->from('post_vote')
    			->where(['post_id' => $postId, 'user_id' => $userId])
    			->one();
    			
    	return ($userPostVoteRow['vote'] === false) ? false : intval($userPostVoteRow['vote']);
    }
    
    public function beforeSave($insert)
    {
    	if(parent::beforeSave($insert))
    	{
    		// avoid to update author_id and creationDate on updates
    		if($insert)
    		{
    			$this->author_id = Yii::$app->user->id;
    			$this->creationDate = date('Y-m-d H:i:s');
    		}
    		return true;
    	}
    	return false;
    }
}
