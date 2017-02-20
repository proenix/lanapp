<?php
/**
* GridView for groups in index map.
* @var $currentGroup Group current group object
* @var $currentPosition Position current position object
* @var $groupChildProvider
* @var $groupChildSearchModel
*/
use yii\helpers\Url;
use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use yii\grid\GridView;
use common\models\Plane;
?>
<div>
    <h4><?= Yii::t('frontend_views_map_groupProvider','Groups') ?></h4>
    <?= Html::tag('a', Yii::t('frontend_views_map_groupProvider','{icon} New group.', [
        'icon' => FA::icon('edit'),
    ]), [
        'data' => [
            'toggle' => 'modal',
            'target' => '#groupForm',
            'group' => (isset($currentGroup->id))?$currentGroup->id:'',
            'pos' => (isset($currentPosition->id))?$currentPosition->id:'',
            'mode' => 'new',
    ]]) ?>
</div>
<?= GridView::widget([
    'dataProvider' => $groupChildProvider,
    'filterModel' => $groupChildSearchModel,
    'options' => ['class' => 'table-responsive grid-view'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'name',
        'description',
        [
            // child elements expandeer for group ?
            'label' => Yii::t('frontend_views_map_groupProvider','Type'),
            'attribute' => 'objectTypeName',
            'format' => 'raw',
            'value' => function($provider) {
                if (isset($provider->object0)) {
                    return Html::tag('a', $provider->object0->type0->name, [
                        'role' => 'button',
                        'tabindex' => 0,
                        'class' => 'tab_position_popover',
                        'data' => [
                            'toggle' => 'popover',
                            'trigger' => 'focus',
                            'placement' => 'left',
                            'title' => '<b>' . $provider->object0->type0->name . '</b>',
                            'html' => true,
                            'content' => '<b>Number of sockets</b>: ' . $provider->object0->type0->sockets . '<br><b>Description:</b> ' . $provider->object0->type0->description,
                        ],
                    ]);
                }
                return;
            }
        ],
        [
            'label' => Yii::t('frontend_views_map_groupProvider','Elements'),
            'attribute' => 'objects0',
            'format' => 'raw',
            'value' => function($provider) {    
                $text = $provider->object0->countObjectsByParentId($provider->id) .  (isset($provider->object0->type0->sockets)?('/' . $provider->object0->type0->sockets):'/-');
                return $text . ' ' . Html::a(Yii::t('frontend_views_map_groupProvider','{icon} Open', [
                    'icon' => FA::icon('plus'),
                ]), Url::current(['group' => $provider->id, 'tab' => 'tab_group', '_pjax' => null]));
            }
        ],
        [
            'label' => Yii::t('frontend_views_map_groupProvider','Menu'),
            'format' => 'raw',
            'value' => function($provider) {
                return Html::tag('a', Yii::t('frontend_views_map_groupProvider','{icon} Edit', [
                    'icon' => FA::icon('edit'),
                ]), [
                    'data' => [
                        'toggle' => 'modal',
                        'target' => '#groupForm',
                        'group' => $provider->id,
                        'pos' => $provider->position,
                        'mode' => 'edit',
                    ]])
                . ' ' .
                Html::tag('a', Yii::t('frontend_views_map_groupProvider','{icon} Delete', [
                    'icon' => FA::icon('trash'),
                ]), [
                    'data' => [
                        'toggle' => 'modal',
                        'target' => '#groupForm',
                        'group' => $provider->id,
                        'pos' => $provider->position,
                        'mode' => 'delete',
                    ]]);
            },
        ],
    ],
]); ?>
