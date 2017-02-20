<?php
/**
* Modal with form for editing and creating and deleting Connections.
* @var $editConnectionModel editConnectionModel
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
<div class="modal fade" id="connectionForm"  role="dialog" aria-labelledby="connectionForm">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php Pjax::begin(['id' => 'containerConnectionPJAX']) ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= Yii::t('frontend_views_map_connectionForm','Connection') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                if (isset($editConnectionModel->status)) {
                    if ($editConnectionModel->status == "success") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-success',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Operation completed successfuly.'),
                        ]);
                    } elseif ($editConnectionModel->status == "error") {
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-warning',
                            ],
                            'body' => Yii::t('frontend_views_map_index','Error while processing operation.'),
                        ]);
                    } elseif ($editConnectionModel->status == "access_error") {
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
                            'body' => $editConnectionModel->status,
                        ]);
                    }
                } ?>

                <?php $form = ActiveForm::begin(['id' => 'connection-form', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editConnectionModel->scenario != $editConnectionModel::SCENARIO_DELETE)?'':'display:none']
                ]); ?>

                <?= $form->field($editConnectionModel, 'cConnection')->hiddenInput()->label(false) ?>

                <?= $form->field($editConnectionModel, 'cDescription')->textInput() ?>

                <?= $form->field($editConnectionModel, 'cStart')->widget(Select2::classname(), [
                    'initValueText' => (isset($editConnectionModel->cStartName))?$editConnectionModel->cStartName:Yii::t('frontend_views_map_connectionForm','Not set.'),
                    'options' => ['placeholder' => Yii::t('frontend_views_map_objectForm','Search for element...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_connectionForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['object-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?= $form->field($editConnectionModel, 'cEnd')->widget(Select2::classname(), [
                    'initValueText' => (isset($editConnectionModel->cEndName))?$editConnectionModel->cEndName:'Not set.',
                    'options' => ['placeholder' => Yii::t('frontend_views_map_connectionForm','Search for element ...')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return '" . Yii::t('frontend_views_map_connectionForm','Waiting for results...') . "' }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['object-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>

                <?php
                    if ($editConnectionModel->scenario == $editConnectionModel::SCENARIO_NEW) {
                        if ($editConnectionModel->status == 'success') {
                            echo Html::submitButton(
                            $editConnectionModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                        } else {
                            echo Html::submitButton(
                            $editConnectionModel->button,
                            ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'new']);
                        }
                    } else {
                        echo Html::submitButton(
                        $editConnectionModel->button,
                        ['class' => 'btn btn-primary', 'name' => 'save-button', 'value' => 'edit']);
                    };
                ?>
                <?php ActiveForm::end(); ?>

                <?php $form = ActiveForm::begin(['id' => 'connection-form-delete', 'options' => [
                    'data' => ['pjax' => true],
                    'style' => ($editConnectionModel->scenario == $editConnectionModel::SCENARIO_DELETE)?'':'display: none']
                ]); ?>

                <?= Yii::t('frontend_views_map_connectionForm','Do you really want to delete this connection? Operation is not reversible.') ?>

                <?= $form->field($editConnectionModel, 'cConnection')->hiddenInput()->label(false) ?>

                <?= Html::submitButton(
                        Yii::t('frontend_views_map_connectionForm','Delete'), [
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
