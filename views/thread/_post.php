<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model app\models\Post */
/* @var $userPostVote mixed */
?>
    <table class="table table-bordered">
    	<tbody>
			<tr>
				<td><p><?= HtmlPurifier::process($model->content) ?></p></td>
			</tr>
			<tr>
				<td>
					<span class="small"><b><?= Html::Encode($model->author->username) ?>, <?= $model->creationDate ?></b></span><br />
					<?= $this->render('_voteManager', ['id' => $model->id, 'score' => $model->score, 'type' => 'post', 'userVote' => $userPostVote]) ?>
				</td>
			</tr>
		</tbody>
	</table>