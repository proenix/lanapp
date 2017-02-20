<?php
/**
* Alert box with message.
* @var $alert string Type of alert.
* @var $message string Message to be displayed.
*/
use yii\bootstrap\Alert;

switch ($alert) {
    case 'success':
        $alert = 'alert-success';
        break;
    case 'error':
        $alert = 'alert-danger';
        break;
    default:
        $alert = 'alert-warning';
        break;
}
?>
<?= Alert::widget([
    'options' => [
        'class' => $alert,
    ],
    'body' => $message,
]);
?>
