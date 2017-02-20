<?php
/* Plane Create View */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\PlaneCreateForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('backend_views_plane_create','Create new plane');
?>
<div class="type-create-type">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('backend_views_plane_create','Please fill out the following fields to create new plane.') ?></p>
    <ul>
        <li><?= Yii::t('backend_views_plane_create','New plane will be activated immedietly.') ?></li>
        <li><?= Yii::t('backend_views_plane_create','Image - please provide image in best possible quality.') ?></li>
    </ul>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-create', 'options' => ['enctype' => 'multipart/form-data']]); ?>

                <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'description')->textArea() ?>

                <?= $form->field($model, 'imageFile')->fileInput() ?>

                <div class="form-group">
                    <?= Html::submitButton(
                        Yii::t('backend_views_plane_create','Create new plane'),
                        ['class' => 'btn btn-primary', 'name' => 'create-button']
                    ) ?>
                    <?= Html::a(Yii::t('backend_views_plane_create','Back'), ['index'], ['class' => 'btn btn-default']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
