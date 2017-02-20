<?php
/**
* Modal with form for editing and creating new and deleting Group.
* @var $editGroupModel editGroupModel
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
<div class="modal fade" id="groupForm"  role="dialog" aria-labelledby="groupForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php Pjax::begin(['id' => 'containerGroupPJAX']) ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $editGroupModel->gName ?></h4>
            </div>
            <div class="modal-body">
                <?php
                if (isset($editGroupModel->status)) {
                    if ($editGroupModel->status == "success") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-success',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Operation completed successfuly.'),
                        ]);
                    } elseif ($editGroupModel->status == "error") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Error while processing operation.'),
                        ]);
                    } elseif ($editGroupModel->status == "access_error") {
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
                            'body' => $editGroupModel->status,
                        ]);
                    }
                } ?>

                <?php $form = ActiveForm::begin(['id' => 'group-form', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editGroupModel->scenario != $editGroupModel::SCENARIO_DELETE)?'':'display: none']
                ]); ?>

                <?= $form->field($editGroupModel, 'gGroup')->hiddenInput()->label(false) ?>

                <?= $form->field($editGroupModel, 'gName')->textInput() ?>

                <?= $form->field($editGroupModel, 'gDescription')->textInput() ?>

                <?= $form->field($editGroupModel, 'gPos')->widget(Select2::classname(), [
                    'initValueText' => $editGroupModel->gPosName,
                    'options' => ['placeholder' => Yii::t('frontend_views_map_groupForm','Search for position...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_groupForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['position-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?= $form->field($editGroupModel, 'gParent')->widget(Select2::classname(), [
                    'initValueText' => $editGroupModel->gParentName,
                    'options' => ['placeholder' => Yii::t('frontend_views_map_groupForm','Search for parent... (optional)')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_groupForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['group-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?= $form->field($editGroupModel, 'gType')->widget(Select2::classname(), [
                    'initValueText' => $editGroupModel->gTypeName,
                    'options' => ['placeholder' => Yii::t('frontend_views_map_groupForm','Search for a device type...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_groupForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['type-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?php
                    if ($editGroupModel->scenario == $editGroupModel::SCENARIO_NEW) {
                        if ($editGroupModel->status == 'success') {
                            echo Html::submitButton(
                            $editGroupModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                        } else {
                            echo Html::submitButton(
                            $editGroupModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'new']);
                        }
                    } else {
                        echo Html::submitButton(
                        $editGroupModel->button,
                        ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                    };
                ?>
                <?php ActiveForm::end(); ?>

                <?php $form = ActiveForm::begin(['id' => 'group-form-delete', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editGroupModel->scenario == $editGroupModel::SCENARIO_DELETE)?'':'display: none']
                ]); ?>

                <?= Yii::t('frontend_views_map_groupForm','Do you really want to delete this group and all of its elements? Operation is not reversible.') ?>

                <?= $form->field($editGroupModel, 'gGroup')->hiddenInput()->label(false) ?>

                <?= Html::submitButton(
                        Yii::t('frontend_views_map_groupForm','Delete'), [
                        'class' => 'btn btn-primary',
                        'name' => 'save-button',
                        'value' => 'delete',
                        'style' => 'display:none'
                    ]) ?>

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                <?php ActiveForm::end(); ?>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
