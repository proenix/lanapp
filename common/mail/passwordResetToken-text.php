<?php
/* @var $this yii\web\View */
/* @var $user common\models\User */

use yii\helpers\Html;

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<?= Yii::t('common_mail_passwordResetToken','Hello {name}',[
    'name' => Html::encode($user->username)
    ]) ?>,

<?= Yii::t('common_mail_passwordResetToken','Follow the link below to reset your password') ?>:

<?= $resetLink ?>
