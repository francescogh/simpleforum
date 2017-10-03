<?php

namespace app\controllers;

use Yii;
use app\models\Post;
use app\models\PostVote;
use yii\web\Controller;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
	/**
     * Manage the vote of the user on the model and the model score.
     * Switch vote:
     * case 1: add vote 1;
     * case -1: add vote -1;
     * case 0: void the already registered vote;
     */
    public function actionAjaxVote()
    {
    	Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;    	

    	if(Yii::$app->user->isGuest) return static::ajaxProtocolErrorResponse('user not authenticated');    	
    	
    	$modelId = \Yii::$app->request->get('id');
    	$userNewVote = \Yii::$app->request->get('vote');    		
    	if($modelId == null or $userNewVote == null) return static::ajaxProtocolErrorResponse('missing parameter/s');
    	$modelId = intval($modelId);
    	$userNewVote = intval($userNewVote);    	
	    	
    	$model = Post::findOne($modelId);    	
    	if(!$model)  return static::ajaxProtocolErrorResponse('inconsistent id parameter');
    	
    	if(!in_array($userNewVote, [1, -1, 0])) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');
    	
    	$userStoredVote = Post::getUserPostVote($modelId, Yii::$app->user->id);
    		
    	if($userNewVote === 0) return static::voidVote($userStoredVote, $model);
    	// else: ok, we have a new vote to store..
    	
    	if(!$userStoredVote) return static::vote($model, $userNewVote);
    	// else: ok, we have a stored vote to transform..
    	
    	return static::changeVote($model, $userStoredVote, $userNewVote);
    }
    
    private static function changeVote($model, $userStoredVote, $userNewVote)
    {
    	if($userStoredVote === $userNewVote) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');

    	$voteModel = PostVote::findOne(['post_id' => $model->id, 'user_id' => Yii::$app->user->id]);
    	$voteModel->vote = $userNewVote;
    	
    	$transaction = \Yii::$app->db->beginTransaction();	    	
    	try
    	{
    		$voteModel->save();    		
    		$model->score += 2 * $userNewVote;
    		$model->save();    		
   			$transaction->commit();
    	}
    	catch (Exception $e)
		{
    		$transaction->rollBack();
    		return static::ajaxProtocolErrorResponse('internal back-end error');
		}
		return static::ajaxProtocolSuccessResponse(['score' => $model->score, 'userStoredVote' => $userNewVote, 'voteMessage' => 'vote switched']);
    }
    
    private static function vote($model, $userNewVote)
    {
    	$voteModel = new PostVote([
    		'post_id' => $model->id,
    		'user_id' => Yii::$app->user->id,
    		'vote' => $userNewVote
    	]);
    	
    	$transaction = \Yii::$app->db->beginTransaction();	    	
    	try
    	{
    		$voteModel->save();
    		$model->score += $userNewVote;
    		$model->save();    		
   			$transaction->commit();
    	}
    	catch (Exception $e)
		{
    		$transaction->rollBack();
    		return static::ajaxProtocolErrorResponse('internal back-end error');
		}
		return static::ajaxProtocolSuccessResponse(['score' => $model->score, 'userStoredVote' => $userNewVote,'voteMessage' => 'vote submitted']);
    }
    
    private static function voidVote($userStoredVote, $model)
    {
    	// has the logged user a post vote to void?
    	if($userStoredVote === false) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');
    		
    	$transaction = \Yii::$app->db->beginTransaction();	    	
    	try
    	{
    		PostVote::deleteAll(['post_id' => $model->id, 'user_id' => Yii::$app->user->id]);    		
    		$model->score -= $userStoredVote;
    		$model->save();    		
   			$transaction->commit();
    	}
    	catch (Exception $e)
		{
    		$transaction->rollBack();
    		return static::ajaxProtocolErrorResponse('internal back-end error');
		}
		return static::ajaxProtocolSuccessResponse(['score' => $model->score, 'userStoredVote' => false, 'voteMessage' => 'vote cancelled']);
    }
    
    private static function ajaxProtocolErrorResponse($errorMessage)
    {
    	return ['result' => 'error', 'errorMessage' => $errorMessage];
    }
    
    private static function ajaxProtocolSuccessResponse($data)
    {
    	return ['result' => 'success', 'data' => $data];
    }
}
