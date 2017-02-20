<?php
/* Change password page View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ChangePasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('frontend_views_site_changePassword','Change password');
?>
<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('frontend_views_site_changePassword','Please choose your new password:')?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'password_old')->passwordInput() ?>

                <?= $form->field($model, 'password_new')->passwordInput() ?>

                <?= $form->field($model, 'password_new2')->passwordInput() ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('frontend_controllers_actionChangePassword','Save'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('frontend_controllers_actionChangePassword','Back'),['site/settings'], ['class' => 'btn btn-default']); ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
