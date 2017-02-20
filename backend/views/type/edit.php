<?php
/* Type Edit View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\TypeEditForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_type_edit','Edit device type');
?>
<div class="type-edit">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_type_edit','Edit device type {name}',[
            'name' => $model->name,
        ]) ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-set-role']); ?>

                <?= $form->field($model, 'name')->textInput() ?>

                <?= $form->field($model, 'description')->textArea() ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_type_edit','Save'),
                        ['class' => 'btn btn-primary', 'name' => 'save-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_type_edit','Back'), ['type/index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
