<?php
/* Main view for MapController */
/* @var $this yii\web\View */
/* @var $plane \common\models\Plane */
/* @var $tab name of tab */
/* @var $planesList List of planes  */
/* @var $currentPosition \common\models\Position Current position on map */
/* @var $currentGroup \common\models\Group Current group */
/* @var $_groupForm View partial */
/* @var $_objectForm View partial */
/* @var $_connectionForm View partial */
/* @var $_positionForm View partial */
/* @var $_groupProvider View partial */
/* @var $_objectProvider View partial */
/* @var $planeConnections View partial */
/* @var $_map View partial */

use yii\bootstrap\Dropdown;
use yii\bootstrap\Button;
use yii\bootstrap\Modal;
use yii\bootstrap\Collapse;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use rmrevin\yii\fontawesome\FA;
use kartik\widgets\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use common\models\Plane;


$this->title = $plane->name;
?>
<div class="col-md-12 col-xs-12">
    <?= Yii::t('frontend_views_map_index','Choose plane') ?> <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-default"><?= Yii::t('frontend_views_map_index','{plane}', [
        'plane' => $plane['name'],
        ]) ?> <b class="caret"></b></a>
        <?php
        // Prepare data for use in Dropdown widget.
        foreach ($planesList as $id => $name) {
            $planesListLabelUrl[] = ['label' => $name, 'url' => Url::to(['map/index', 'map' => $id])];
        }
        echo Dropdown::widget([
            'items' => $planesListLabelUrl,
        ]);
        ?>
    </div>
<div class="row">

    <!-- map -->
    <?= $_map ?>

    <div class="col-md-6 col-sm-12 col-xs-12">
        <div>
            <h2>
                <a role="button" data-toggle="collapse" href="#collapsePlaneDescription" aria-expanded="false" aria-controls="collapseExample" style="text-decoration: none">
                    <?= $plane->name ?>
                </a>
            </h2>

            <div class="collapse" id="collapsePlaneDescription">
                <div class="well">
                    <?= $plane->description ?>
                </div>
            </div>

            <!-- Container for AJAX responses. -->
            <div id="response">
            </div>

        <!-- Tab panes -->
        <?php
        /**
        * Render all groups connected with selected position (point on map)
        * Uses PJAX with id #containerPJAX
        * @todo implement serarch model
        */
        Pjax::begin(['id' => 'containerPJAX']); ?>
        <!-- Nav tabs -->
        <!-- <ul class="nav nav-tabs" role="tablist" id="tab_index">
            <li role="presentation" class="<?= ($tab == 'tab_group')?'active':'' ?>">
                <a href="#tab_group" aria-controls="tab_group" role="tab" data-toggle="tab"><?= Yii::t('frontend_views_map_index','Group') ?></a>
            </li>
        </ul> -->
        <div id="containerPJAX_pos" style="display:none" data-pos="<?= (isset($currentPosition->id))?$currentPosition->id:'' ?>"></div>
        <div id="containerPJAX_connections" style="display:none" data='<?= $planeConnections ?>'></div>

        <div class="">
            <h3>
                <?php
                    // Shows current position name and current group name.
                    if (isset($currentPosition->name)) {
                        echo Html::a($currentPosition->name, Url::current(['pos' => $currentPosition->id, 'group' => null, 'tab' => 'tab_group'])) . ' ';
                        if (isset($currentGroup))
                            echo ' ' . FA::icon('long-arrow-right') . ' ';
                    }
                    function getParent($object) {
                        if (isset($object->parent0)) {
                            echo getParent($object->parent0);
                            echo FA::icon('long-arrow-right') . ' ';
                        }
                        if (isset($object->name)) {
                            echo Html::a($object->name, Url::current(['group' => $object->id, 'tab' => 'tab_group'])) . ' ';
                        }
                        return null;
                    }
                    // Recursive show of groups
                    echo getParent($currentGroup) . (isset($currentGroup->id))?(Html::tag('a', FA::icon('pencil'), [
                        'data' => [
                            'toggle' => 'modal',
                            'target' => '#groupForm',
                            'group' => $currentGroup->id,
                            'pos' => $currentPosition->id,
                            'mode' => 'edit',
                        ]])):'';
                ?>
            </h3>

            <!-- Group Provider -->
            <?= $_groupProvider ?>

            <!-- Object Provider -->
            <?= $_objectProvider ?>
        </div>
        <?php Pjax::end(); ?>

        </div>

        <?php Pjax::begin(['id'=>'containerPJAX', 'linkSelector'=>'#data a']); ?>
        <?php Pjax::end(); ?>

    </div>
</div>

    <!-- BEGIN Modal1 -->
    <?= $_groupForm ?>
    <!-- END Modal1 -->

    <!-- BEGIN Modal2 -->
    <?= $_objectForm ?>
    <!-- END Modal2 -->

    <!-- BEGIN Modal3 -->
    <?= $_positionForm ?>
    <!-- END Modal3 -->

    <!-- BEGIN Modal4 -->
    <?= $_connectionForm ?>
    <!-- END Modal4 -->

</div>
<?php
$js2 = <<< 'SCRIPT'
// groupForm AJAX
$('#groupForm').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);
    // Extract info from data-* attributes
    var posID = button.data('pos');
    var groupID = button.data('group');

    // Reset all errors and form data present on form.
    modal.find('#group-form').trigger('reset');
    // Remove alert from top of modal.
    modal.find('div.alert').remove();

    // Update link for form to process ajax corectly
    modal.find('#group-form').attr('action', $(location).attr('href'));
    modal.find('#group-form-delete').attr('action', $(location).attr('href'));
    modal.find('#group-form').hide();
    modal.find('#group-form-delete').hide();
    if (button.data('mode') == 'edit') {
        modal.find('#group-form').show();

        $.ajax({
            type: 'POST',
            url: '/map/group-form',
            data: {
                gGroup: groupID,
                gPos: posID,
                mode: 'edit',
            },
            success: function (data) {
                modal.find('.modal-title').text(data.gName);
                modal.find('#group-form [name="save-button"]').val('edit');
                modal.find('#group-form [name="save-button"]').text(data.button);
                modal.find('[name="EditGroupModel[gGroup]"]').val(data.gGroup);
                modal.find('[name="EditGroupModel[gName]"]').val(data.gName);
                modal.find('[name="EditGroupModel[gDescription]"]').val(data.gDescription);
                if (data.gParent == null) {
                    modal.find('#editgroupmodel-gparent').html('<option value="0">'+data.gParentName+'</option>').val(0).trigger('change');
                } else {
                    modal.find('#editgroupmodel-gparent').html('<option value="'+data.gParent+'">'+data.gParentName+'</option>').val(data.gParent).trigger('change');
                }
                modal.find('#editgroupmodel-gpos').html('<option value="'+data.gPos+'">'+data.gPosName+'</option>').val(data.gPos).trigger('change');
                if (data.gType == null) {
                    modal.find('#editgroupmodel-gtype').html('<option value="0">No type</option>').val(0).trigger('change');
                } else {
                    modal.find('#editgroupmodel-gtype').html('<option value="'+data.gType+'">'+data.gTypeName+'</option>').val(data.gType).trigger('change');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'new') {
        modal.find('#group-form').show();
        modal.find('.modal-title').text('New group');
        modal.find('#group-form [name="save-button"]').val('new');
        modal.find('[name="EditGroupModel[gGroup]"]').val('0');
        modal.find('[name="EditGroupModel[gName]"]').val('');
        modal.find('[name="EditGroupModel[gDescription]"]').val('');
        $.ajax({
            type: 'POST',
            url: '/map/group-form',
            data: {
                gGroup: groupID,
                gPos: posID,
                mode: 'new',
            },
            success: function (data) {
                modal.find('#group-form [name="save-button"]').text(data.button);
                if (data.gParent == null) {
                    modal.find('#editgroupmodel-gparent').html('<option value="0">'+data.gParentName+'</option>').val(0).trigger('change');
                } else {
                    modal.find('#editgroupmodel-gparent').html('<option value="'+data.gParent+'">'+data.gParentName+'</option>').val(data.gParent).trigger('change');
                }
                modal.find('#editgroupmodel-gtype').html('').trigger('change');
                modal.find('#editgroupmodel-gpos').html('<option value="'+data.gPos+'">'+data.gPosName+'</option>').val(data.gPos).trigger('change');
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'delete') {
        modal.find('[name="EditGroupModel[gGroup]"]').val(groupID);
        modal.find('#group-form-delete [name="save-button"]').show();
        modal.find('#group-form-delete').show();
    }
});

// objectForm AJAX
$('#objectForm').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);

    // Extract info from data-* attributes
    var objectID = button.data('object');
    var parentID = button.data('parent')

    // Reset all errors and form data present on form.
    modal.find('#object-form').trigger('reset');
    // Remove alert from top od modal.
    modal.find('div.alert').remove();

    // Update link for form to process ajax correctly
    modal.find('#object-form').attr('action', $(location).attr('href'));
    modal.find('#object-form-delete').attr('action', $(location).attr('href'));
    modal.find('#object-form').hide();
    modal.find('#object-form-delete').hide();
    if (button.data('mode') == 'edit') {
        modal.find('#object-form').show();
        $.ajax({
            type: 'POST',
            url: '/map/object-form',
            data: {
                oObject: objectID,
                oParent: parentID,
                mode: 'edit',
            },
            success: function (data) {
                modal.find('.modal-title').text(data.oName);
                modal.find('#object-form [name="save-button"]').val('edit');
                modal.find('#object-form [name="save-button"]').text(data.button);
                modal.find('[name="EditObjectModel[oObject]"]').val(data.oObject);
                modal.find('[name="EditObjectModel[oName]"]').val(data.oName);
                modal.find('[name="EditObjectModel[oDescription]"]').val(data.oDescription);
                if (data.oParent == null) {
                    modal.find('#editobjectmodel-oparent').html('<option value="0">No parent</option>').val(0).trigger('change');
                } else {
                    modal.find('#editobjectmodel-oparent').html('<option value="'+data.oParent+'">'+data.oParentName+'</option>').val(data.oParent).trigger('change');
                }
                if (data.oType == null) {
                    modal.find('#editobjectmodel-otype').html('<option value="0">No type</option>').val(0).trigger('change');
                } else {
                    modal.find('#editobjectmodel-otype').html('<option value="'+data.oType+'">'+data.oTypeName+'</option>').val(data.oType).trigger('change');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'new') {
        modal.find('#object-form').show();
        modal.find('.modal-title').text('New element');
        modal.find('#object-form [name="save-button"]').val('new');
        modal.find('[name="EditObjectModel[oObject]"]').val('');
        modal.find('[name="EditObjectModel[oParent]"]').val('0');
        modal.find('[name="EditObjectModel[oName]"]').val('');
        modal.find('[name="EditObjectModel[oDescription]"]').val('');
        $.ajax({
            type: 'POST',
            url: '/map/object-form',
            data: {
                oObject: objectID,
                oParent: parentID,
                mode: 'new',
            },
            success: function (data) {
                modal.find('[name="save-button"]').text(data.button);
                if (data.oParent == null) {
                    modal.find('#editobjectmodel-oparent').html('<option value="0">No parent</option>').val(0).trigger('change');
                } else {
                    modal.find('#editobjectmodel-oparent').html('<option value="'+data.oParent+'">'+data.oParentName+'</option>').val(data.oParent).trigger('change');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'delete') {
        modal.find('[name="EditObjectModel[oObject]"]').val(objectID);
        modal.find('#object-form-delete').show();
        modal.find('#object-form-delete [name="save-button"]').show();
    }
});

// connectionForm AJAX
$('#connectionForm').on('show.bs.modal', function (event) {
    // Hide popover from where modal is opened.
    $('.tab_position_popover').popover('hide');
    var button = $(event.relatedTarget);
    var modal = $(this);

    // Extract info from data-* attributes
    var connectionID = button.data('connection');

    // Reset all errors and form data present on form.
    modal.find('#connection-form').trigger('reset');
    // Remove alert from top od modal.
    modal.find('div.alert').remove();

    // Update link for form to process ajax correctly
    modal.find('#connection-form').attr('action', $(location).attr('href'));
    modal.find('#connection-form-delete').attr('action', $(location).attr('href'));
    modal.find('#connection-form').hide();
    modal.find('#connection-form-delete').hide();
    if (button.data('mode') == 'edit') {
        modal.find('#connection-form').show();

        modal.find('#connection-form [name="save-button"]').val('edit');
        modal.find('[name="EditConnectionModel[cConnection]"]').val(connectionID);
        $.ajax({
            type: 'POST',
            url: '/map/connection-form',
            data: {
                cConnection: connectionID,
                mode: 'edit',
            },
            success: function (data) {
                modal.find('#connection-form [name="save-button"]').text(data.button);
                if (data.cStart == null) {
                    modal.find('#editconnectionmodel-cstart').html('').val(0).trigger('change');
                } else {
                    modal.find('#editconnectionmodel-cstart').html('<option value="'+data.cStart+'">'+data.cStartName+'</option>').val(data.cStart).trigger('change');
                }
                if (data.cEnd == null) {
                    modal.find('#editconnectionmodel-cend').html('').val(0).trigger('change');
                } else {
                    modal.find('#editconnectionmodel-cend').html('<option value="'+data.cEnd+'">'+data.cEndName+'</option>').val(data.cEnd).trigger('change');
                }
                modal.find('[name="EditConnectionModel[cDescription]"]').val(data.cDescription);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'new') {
        modal.find('#connection-form').show();
        var startID = button.data('start');
        var endID = button.data('end');

        modal.find('#connection-form [name="save-button"]').val('new');
        $.ajax({
            type: 'POST',
            url: '/map/connection-form',
            data: {
                cStart: startID,
                cEnd: endID,
                mode: 'new',
            },
            success: function (data) {
                modal.find('[name="save-button"]').text(data.button);
                if (data.cStart == null) {
                    modal.find('#editconnectionmodel-cstart').html('').val(0).trigger('change');
                } else {
                    modal.find('#editconnectionmodel-cstart').html('<option value="'+data.cStart+'">'+data.cStartName+'</option>').val(data.cStart).trigger('change');
                }
                if (data.cEnd == null) {
                    modal.find('#editconnectionmodel-cend').html('').val(0).trigger('change');
                } else {
                    modal.find('#editconnectionmodel-cend').html('<option value="'+data.cEnd+'">'+data.cEndName+'</option>').val(data.cEnd).trigger('change');
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error in AJAX request.');
            }
        })
    } else if (button.data('mode') == 'delete') {
        modal.find('[name="EditConnectionModel[cConnection]"]').val(connectionID);
        modal.find('#connection-form-delete').show();
        modal.find('#connection-form-delete [name="save-button"]').show();
    }
});
$('#groupForm').on('hide.bs.modal', function (event) {
    // Reload #containerPJAX after modal closed.
    $.pjax.reload({container:'#containerPJAX', url: $(location).attr('href')});
});
$('#objectForm').on('hide.bs.modal', function (event) {
    // Reload #containerPJAX after modal closed.
    $.pjax.reload({container:'#containerPJAX', url: $(location).attr('href')});
});
$('#connectionForm').on('hide.bs.modal', function (event) {
    // Reload #containerPJAX after modal closed.
    $.pjax.reload({container:'#containerPJAX', url: $(location).attr('href')});
});
$('#containerPJAX').on('ready pjax:success', function(event, data, status, xhr, options){
    // Redraw connections and active poisition after #containerPJAX is processed.
    redrawPositionMap();
    // Add popover to elements that have class tab_position_popover after pjax is finished on #containerPJAX
    $("#containerPJAX a.tab_position_popover").popover();
});
$('#containerPJAX').on('show.bs.popover', '.tab_position_popover', function() {
    // Hide popovers for connection if other are open.
    $('.popover').not(this).popover('hide');
});
SCRIPT;
$this->registerJs($js2);
?>
