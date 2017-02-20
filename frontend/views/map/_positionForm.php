<?php
/**
* Modal with form for creating and editing positions
* @var $editPositionModel EditPositionModel
*/
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use kartik\widgets\Select2;
use yii\web\JsExpression;
?>
<div class="modal fade" id="positionForm"  role="dialog" aria-labelledby="positionForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php Pjax::begin(['id' => 'containerPositionPJAX']) ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Yii::t('frontend_views_map_positionForm','Formo') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                if (isset($editPositionModel->status)) {
                    if ($editPositionModel->status == "success") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-success',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Operation completed successfuly.'),
                        ]);
                    } elseif ($editPositionModel->status == "error") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Error while processing operation.'),
                        ]);
                    } elseif ($editPositionModel->status == "access_error") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => Yii::t('frontend_views_map_index','You don\'t have sufficient privileges to perform this action.'),
                        ]);
                    } else {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => $editPositionModel->status,
                        ]);
                    }
                } ?>

                <?php $form = ActiveForm::begin(['id' => 'position-form', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editPositionModel->scenario != $editPositionModel::SCENARIO_DELETE)?'':'display:none']
                ]); ?>

                <?= $form->field($editPositionModel, 'pPosition')->hiddenInput()->label(false) ?>

                <?= $form->field($editPositionModel, 'pName')->textInput() ?>

                <?= $form->field($editPositionModel, 'pDescription')->textInput() ?>

                <?= $form->field($editPositionModel, 'pX')->textInput(['readonly' => true]) ?>
                <?= $form->field($editPositionModel, 'pY')->textInput(['readonly' => true]) ?>

                <?php
                    echo Html::submitButton(
                        Yii::t('frontend_views_map_positionForm','Save'),
                        ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                ?>
                <?php ActiveForm::end(); ?>

                <?php $form = ActiveForm::begin(['id' => 'position-form-delete', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editPositionModel->scenario == $editPositionModel::SCENARIO_DELETE)?'':'display: none']
                ]); ?>
                <?= Yii::t('frontend_views_map_positionForm','Do you really want to delete this position on map? Operation is not reversible.') ?>
                <?= $form->field($editPositionModel, 'pPosition')->hiddenInput()->label(false) ?>

                <?php
                    echo Html::submitButton(
                        Yii::t('frontend_views_map_positionForm','Delete'),[
                            'class' => 'btn btn-primary',
                            'name' => 'save-button',
                            'value' => 'delete',
                            'style' => ($editPositionModel->status == $editPositionModel::STATUS_SUCCESS)?'display: none':'',
                        ]);
                ?>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <?php ActiveForm::end(); ?>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
