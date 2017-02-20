<?php
/* $password string from backend/models/createUserForm */

use yii\helpers\Html;
?>
<?= Yii::t('common_mail_userCreated','Hello') ?>,

<?= Yii::t('common_mail_userCreated','Your account to {app} was just created. Your password is',[
        'app' => Html::encode(Yii::$app->name),
    ]) ?>:

<?= $password ?>
