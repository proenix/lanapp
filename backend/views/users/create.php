<?php
/* User Create View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\UserCreateForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_users_create','Create user');
?>
<div class="user-create-user">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_users_create','Please fill out the following fields to create new user:') ?></p>
    <ul>
        <li><?= Yii::t('backend_views_users_create','New user will be activated and gain viewer role.') ?></li>
        <li><?= Yii::t('backend_views_users_create','Password will be send to user to provided email.') ?></li>
    </ul>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-create']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'email') ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_users_create','Create new user'),
                        ['class' => 'btn btn-primary', 'name' => 'create-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_users_create','Back'), ['users/index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
