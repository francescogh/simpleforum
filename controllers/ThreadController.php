<?php

namespace app\controllers;

use Yii;
use app\models\Thread;
use app\models\ThreadVote;
use app\models\ThreadSearch;
use app\models\Post;
use app\components\ViewsList;
use yii\web\Cookie;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * ThreadController implements the CRUD actions for Thread model.
 */
class ThreadController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'create-new-post'],
                'rules' => [
                    [
                        'actions' => ['create', 'create-new-post'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],            
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
            		'create-new-post' => ['POST']
                ],
            ],
        ];
    }

    /**
     * Lists all Thread models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ThreadSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Thread model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);
    	
    	$jsonOldViewsList = Yii::$app->request->cookies->getValue('views-list', Json::encode(array()));
    	    	
    	$newViewsList = ViewsList::create($jsonOldViewsList); 
    	
    	$wasCounted = $newViewsList->add($id);    	
    	if($wasCounted)
    	{
    		$model->views += 1;
    		$model->save(false);
    	}

    	
    	// add the cookie to the response to be sent		
    	Yii::$app->response->cookies->add(new Cookie([
		    'name' => 'views-list',
		    'value' => $newViewsList->getSerialized(),
    		'expire' => time() + ViewsList::EXPIRING_TIME
		]));
		    	
    	// initialize the thread as unvoted
    	$userThreadVote = false;
    	
    	// initialize all the thread posts as unvoted
    	$userPostVotes = array();
    	foreach($model->posts as $post) $userPostVotes[$post->id] = false;
    	
    	if(!Yii::$app->user->isGuest)
    	{
    		$userThreadVote = Thread::getUserThreadVote($id, Yii::$app->user->id);
    		$userPostVotes = Post::getUserPostVotes($id, Yii::$app->user->id);
    	}    			
    	
    	$newPost = new Post();    	

        return $this->render('view', [
            'model' => $model,
            'posts' => $model->posts,
        	'newPost' => $newPost,
        	'userThreadVote' => $userThreadVote,
        	'userPostVotes' => $userPostVotes,
        ]);
    } 

    /**
     * Create a new post for this thread
     * @param integer $id
     * @return mixed
     */
    public function actionCreateNewPost($id)
    {    	
    	$newPost = new Post();
    	
    	if($newPost->load(Yii::$app->request->post(), "Post"))    	
    	{
    		$newPost->thread_id = $id;
    		
    		if ($newPost->save())
    		{
    			return $this->redirect(['view', 'id' => $id]); 
    		}
    	}    	
    	
    	$model = $this->findModel($id);
    	
    	$userThreadVote = Thread::getUserThreadVote($id, Yii::$app->user->id);
    	$userPostVotes = Post::getUserPostVotes($id, Yii::$app->user->id);
  	
        return $this->render('view', [
            'model' => $model,
            'posts' => $model->posts,
        	'newPost' => $newPost,
        	'userThreadVote' => $userThreadVote,
        	'userPostVotes' => $userPostVotes,
        ]);
    }

    /**
     * Creates a new Thread model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Thread();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
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
	    	
    	$model = Thread::findOne($modelId);    	
    	if(!$model)  return static::ajaxProtocolErrorResponse('inconsistent id parameter');
    	
    	if(!in_array($userNewVote, [1, -1, 0])) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');
    	
    	$userStoredVote = Thread::getUserThreadVote($modelId, Yii::$app->user->id);
    		
    	if($userNewVote === 0) return static::voidVote($userStoredVote, $model);
    	// else: ok, we have a new vote to store..
    	
    	if(!$userStoredVote) return static::vote($model, $userNewVote);
    	// else: ok, we have a stored vote to transform..
    	
    	return static::changeVote($model, $userStoredVote, $userNewVote);
    }
    
    private static function changeVote($model, $userStoredVote, $userNewVote)
    {
    	if($userStoredVote === $userNewVote) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');

    	$voteModel = ThreadVote::findOne(['thread_id' => $model->id, 'user_id' => Yii::$app->user->id]);
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
    	$voteModel = new ThreadVote([
    		'thread_id' => $model->id,
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
    	// has the logged user a thread vote to void?
    	if($userStoredVote === false) return static::ajaxProtocolErrorResponse('inconsistent vote parameter');
    		
    	$transaction = \Yii::$app->db->beginTransaction();	    	
    	try
    	{
    		ThreadVote::deleteAll(['thread_id' => $model->id, 'user_id' => Yii::$app->user->id]);    		
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

    /**
     * Finds the Thread model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Thread the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Thread::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
