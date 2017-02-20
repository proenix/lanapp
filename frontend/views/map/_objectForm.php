<?php
/**
* Modal with form for editing and creating and deleting Objects.
* @var $editObjectModel editObjectModel
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
<div class="modal fade" id="objectForm"  role="dialog" aria-labelledby="objectForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php Pjax::begin(['id' => 'containerObjectPJAX']) ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $editObjectModel->oName ?></h4>
            </div>
            <div class="modal-body">
                <?php
                if (isset($editObjectModel->status)) {
                    if ($editObjectModel->status == "success") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-success',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Operation completed successfuly.'),
                        ]);
                    } elseif ($editObjectModel->status == "error") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Error while processing operation.'),
                        ]);
                    } elseif ($editObjectModel->status == "access_error") {
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
                            'body' => $editObjectModel->status,
                        ]);
                    }
                } ?>

                <?php $form = ActiveForm::begin(['id' => 'object-form', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editObjectModel->scenario != $editObjectModel::SCENARIO_DELETE)?'':'display:none']
                ]); ?>

                <?= $form->field($editObjectModel, 'oObject')->hiddenInput()->label(false) ?>

                <?= $form->field($editObjectModel, 'oName')->textInput() ?>

                <?= $form->field($editObjectModel, 'oDescription')->textInput() ?>

                <?= $form->field($editObjectModel, 'oParent')->widget(Select2::classname(), [
                    'initValueText' => (isset($editObjectModel->oParentName))?$editObjectModel->oParentName:Yii::t('frontend_views_map_objectForm','Parent not set.'),
                    'options' => ['placeholder' => Yii::t('frontend_views_map_objectForm','Search for parent...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_objectForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['group-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?= $form->field($editObjectModel, 'oType')->widget(Select2::classname(), [
                    'initValueText' => (isset($editObjectModel->oTypeName))?$editObjectModel->oTypeName:'No type.',
                    'options' => ['placeholder' => Yii::t('frontend_views_map_objectForm','Search for element type...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_objectForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['type-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?php
                    if ($editObjectModel->scenario == $editObjectModel::SCENARIO_NEW) {
                        if ($editObjectModel->status == 'success') {
                            echo Html::submitButton(
                            $editObjectModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                        } else {
                            echo Html::submitButton(
                            $editObjectModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'new']);
                        }
                    } else {
                        echo Html::submitButton(
                        $editObjectModel->button,
                        ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                    };
                ?>
                <?php ActiveForm::end(); ?>

                <?php $form = ActiveForm::begin(['id' => 'object-form-delete', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editObjectModel->scenario == $editObjectModel::SCENARIO_DELETE)?'':'display: none']
                ]); ?>

                <?= Yii::t('frontend_views_map_objectForm','Do you really want to delete this element? Operation is not reversible.') ?>

                <?= $form->field($editObjectModel, 'oObject')->hiddenInput()->label(false) ?>

                <?= Html::submitButton(
                        Yii::t('frontend_views_map_objectForm','Delete'), [
                            'class' => 'btn btn-primary',
                            'name' => 'save-button',
                            'value' => 'delete',
                            'style' => 'display: none',
                        ]) ?>

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                <?php ActiveForm::end(); ?>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
