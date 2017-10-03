<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Thread */
/* @var $id integer */
/* @var $score integer */
/* @var $type string */
/* @var $userVote mixed */

$btnVotePosState = $userVote === 1 ? 'on' : 'off';
$btnVoteNegState = $userVote === -1 ? 'on' : 'off';
$btnVotePosClass = 'btn-vote-pos ' . $btnVotePosState;
$btnVoteNegClass = 'btn-vote-neg ' . $btnVoteNegState;
?>
<span class="vote_manager">
	score: <span class="score" id="<?= 'score-'.$type.'-'.$id ?>"><?= $score ?></span>
	<?php if (!Yii::$app->user->isGuest): ?>
	<span class="vote-buttons">
		<?= Html::a('+1', '#', ['class' => $btnVotePosClass, 'id' => 'bvp-'.$type.'-'.$id]) ?>
		<?= Html::a('-1', '#', ['class' => $btnVoteNegClass, 'id' => 'bvn-'.$type.'-'.$id]) ?>
	</span>
	<span class="vote-message" id="<?= 'vm-'.$type.'-'.$id ?>"></span>
	<?php endif; ?>
</span>
