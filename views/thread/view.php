<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\Thread */
/* @var $userThreadVote mixed */
/* @var $userPostVotes array */
/* @var $newPost app\models\Post */
/* @var $posts array */

$this->registerCssFile("@web/vote_manager/css/vote_manager.css");
$this->registerJsFile("@web/vote_manager/js/vote_manager.js", ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJs(
	"voteThreadUrl = '" . Url::to(["thread/ajax-vote"]) . "';"
	."votePostUrl = '" . Url::to(["post/ajax-vote"]) . "';",	
	View::POS_READY
);

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Threads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="thread-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <table class="table table-bordered">
    	<tbody>
			<tr>
				<td><p><?= HTMLPurifier::process($model->content) ?></p></td>
			</tr>
			<tr>
				<td>
					<span class="small"><b><?= Html::encode($model->author->username) ?>, <?= $model->creationDate ?></b></span><br />
					<?= $this->render('_voteManager', ['id' => $model->id, 'score' => $model->score, 'type' => 'thread', 'userVote' => $userThreadVote]) ?>
				</td>
			</tr>
		</tbody>
	</table>
    
    <p>Total posts: <?= count($posts) ?></p>    

		<?php
	    	foreach($posts as $postModel)
			{
				echo $this->render('_post', ['model' => $postModel, 'userPostVote' => $userPostVotes[$postModel->id]]);
			}
	    ?> 
	    
	    <div class="thread-create">

    <?= $this->render('_postCreateForm', ['model' => $model, 'newPost' => $newPost]) ?>

</div>

</div>
