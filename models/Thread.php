<?php

namespace app\models;

use Yii;
use app\components\BadPatternsValidator;

/**
 * This is the model class for table "thread".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $creationDate
 * @property integer $author_id
 * @property integer $score
 * @property integer $views
 *
 * @property User $author
 */
class Thread extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'thread';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
    	$urlPattern = '/(?:^|\s)(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#]|\s)/i';
    	
        return [
            [['title', 'content'], 'required'],
            [['content'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 50],
            [['content'], 'match', 'pattern' => $urlPattern, 'not' => true], // content can't contain urls
            [['content'], BadPatternsValidator::className()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'creationDate' => 'Creation Date',
            'author_id' => 'Author ID',
        	'score' => 'Score',
        	'views' => 'Views',
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
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['thread_id' => 'id'])->orderBy("creationDate ASC");
    }
    
    /**
     * get a thread vote by an user
     * returning false if not found any vote, else return the vote (-1 or 1) 
     * 
     * @param $threadId integer
     * @param $userId integer 
     * @return mixed
     */
    public static function getUserThreadVote($threadId, $userId)
    {
    	$userThreadVoteRow = (new \yii\db\Query())
    			->select('vote')
    			->from('thread_vote')
    			->where(['thread_id' => $threadId, 'user_id' => $userId])
    			->one();
    			
    	return ($userThreadVoteRow['vote'] === false) ? false : intval($userThreadVoteRow['vote']);
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
