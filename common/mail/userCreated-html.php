<?php
/* $password string from backend/models/createUserForm */

use yii\helpers\Html;
?>
<div class="new-user">
    <p><?= Yii::t('common_mail_userCreated','Hello') ?>,</p>

    <p><?= Yii::t('common_mail_userCreated','Your account to {app} was just created. Your password is',[
            'app' => Html::encode(Yii::$app->name),
        ]) ?>:</p>

    <p><?= $password ?></p>
</div>
