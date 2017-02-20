<?php
/* User Set-role View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\UserSetRoleForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_users_set-role','Set role user');
?>
<div class="user-set-role">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_users_set-role','Change role of user {username}',[
            'username' => $model->getUsername(),
        ]) ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-set-role']); ?>

                <?= $form->field($model, 'role')->dropdownList(
					$roles,
					['prompt'=> Yii::t('backend_views_users_set-role','Select Role')]
				) ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_users_set-role','Set role'),
                        ['class' => 'btn btn-primary', 'name' => 'set-role-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_users_set-role','Back'), ['users/index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
