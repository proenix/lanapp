<?php
/* Type Create View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\TypeCreateForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_type_create','Create new device type');
?>
<div class="type-create-type">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_type_create','Please fill out the following fields to create new device type.') ?></p>
    <ul>
        <li><?= Yii::t('backend_views_type_create','New device will be activated immedietly.') ?></li>
        <li><?= Yii::t('backend_views_type_create','Sockets - additional object created in as a childs of device. Used to create connections between devices - eg. ports on switch') ?></li>
    </ul>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-create']); ?>

                <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'description')->textArea() ?>

                <?= $form->field($model, 'sockets')->input('number', ['min' => 0, 'max' => 256]) ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_type_create','Create new device type'),
                        ['class' => 'btn btn-primary', 'name' => 'create-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_type_create','Back'), ['type/index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
