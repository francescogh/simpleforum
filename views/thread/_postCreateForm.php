<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Thread */
/* @var $newPost app\models\Post */
/* @var $form yii\widgets\ActiveForm */
?>

<h2>Leave your comment..</h2>

<div class="post-form">

    <?php $form = ActiveForm::begin(['action' =>['thread/create-new-post', 'id' => $model->id ]]); ?>
    
    <?= $form->field($newPost, 'content')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($newPost->isNewRecord ? 'Create' : 'Update', ['class' => $newPost->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
