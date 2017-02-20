<?php
/* Plane Edit View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\PlaneEditForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_plane_edit','Edit plane.');
?>
<div class="type-edit">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_plane_edit','Edit plane {name}',[
            'name' => $model->name,
        ]) ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-set-role', 'options' => ['enctype' => 'multipart/form-data']]); ?>

                <?= $form->field($model, 'name')->textInput() ?>

                <?= $form->field($model, 'description')->textArea() ?>

                <?= $form->field($model, 'imageFile')->fileInput() ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_plane_edit','Save'),
                        ['class' => 'btn btn-primary', 'name' => 'save-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_plane_edit','Back'), ['index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
