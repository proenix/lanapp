<?php
/**
* GridView for groups in index map.
* @var $currentGroup Group current group object
* @var $currentPosition Position current position object
* @var $objectProvider
* @var $objectSearchModel
*/
use yii\helpers\Url;
use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use yii\grid\GridView;
use common\models\Plane;
?>
<h4><?= Yii::t('frontend_views_map_objectProvider','Elements') ?></h4>
<?= (isset($currentGroup->id))?Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} New element.', [
    'icon' => FA::icon('edit'),
]), [
    'data' => [
        'toggle' => 'modal',
        'target' => '#objectForm',
        'object' => null,
        'parent' => (isset($currentGroup->id))?$currentGroup->id:'',
        'mode' => 'new',
]]):'' ?>


<?= GridView::widget([
    'dataProvider' => $objectProvider,
    'filterModel' => $objectSearchModel,
    'options' => ['class' => 'table-responsive grid-view'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'name',
        'description',
        [
            'label' => Yii::t('frontend_views_map_objectProvider','Connection in'),
            'attribute' => 'connection0',
            'format' => 'raw',
            'value' => function($provider) {
                $con = '';
                if (isset($provider->connection0)) {
                    // in
                    $content = '<b>' . Yii::t('frontend_views_map_connectionForm','Description') . ':</b> '
                        . $provider->connection0->description
                        . '<br>'
                        . '<b>' . Yii::t('frontend_views_map_objectProvider','Object\'s group') . ':</b> '
                        . Html::a($provider->connection0->end1->parent0->name,
                            Url::current(['pos' => $provider->connection0->end1->position, 'group' => $provider->connection0->end1->parent, 'tab' => 'tab_group']))
                        . '<br> '
                        . Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Edit', [
                            'icon' => FA::icon('edit'),
                        ]), [
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#connectionForm',
                                'connection' => $provider->connection0->id,
                                'mode' => 'edit',
                            ]
                        ]) . ' ' .
                        Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Remove', [
                            'icon' => FA::icon('trash'),
                        ]), [
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#connectionForm',
                                'connection' => $provider->connection0->id,
                                'mode' => 'delete',
                            ]
                        ]);
                    return Html::tag('a', $provider->connection0->end1->name, [
                        'role' => 'button',
                        'tabindex' => 0,
                        'class' => 'tab_position_popover',
                        'data' => [
                            'toggle' => 'popover',
                            'placement' => 'left',
                            'title' => '<b>' . Yii::t('frontend_views_map_connectionForm','Link in from {object}', [
                                    'object' => $provider->connection0->end1->name
                                ])  . '</b>',
                            'html' => true,
                            'content' => $content,
                        ],
                    ]);
                } else {
                    return Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} New', [
                        'icon' => FA::icon('plus'),
                    ]), [
                        'data' => [
                            'toggle' => 'modal',
                            'target' => '#connectionForm',
                            'start' => $provider->id,
                            'mode' => 'new',
                        ]
                    ]);
                }
            }
        ],
        [
            'label' => Yii::t('frontend_views_map_objectProvider','Connection out'),
            'attribute' => 'connection1',
            'format' => 'raw',
            'value' => function($provider) {
                if (isset($provider->connection1)) {
                    // out
                    $content = '<b>' . Yii::t('frontend_views_map_connectionForm','Description') . ':</b> '
                        . $provider->connection1->description
                        . '<br>'
                        . '<b>' . Yii::t('frontend_views_map_objectProvider','Object\'s group') . ':</b> '
                        . Html::a($provider->connection1->end0->parent0->name,
                            Url::current(['pos' => $provider->connection1->end0->position, 'group' => $provider->connection1->end0->parent, 'tab' => 'tab_group']))
                        . '<br> '
                        . Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Edit', [
                            'icon' => FA::icon('edit'),
                        ]), [
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#connectionForm',
                                'connection' => $provider->connection1->id,
                                'mode' => 'edit',
                            ]
                        ]) . ' ' .
                        Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Remove', [
                            'icon' => FA::icon('trash'),
                        ]), [
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#connectionForm',
                                'connection' => $provider->connection1->id,
                                'mode' => 'delete',
                            ]
                        ]);
                    return Html::tag('a', $provider->connection1->end0->name, [
                        'role' => 'button',
                        'tabindex' => 0,
                        'class' => 'tab_position_popover',
                        'data' => [
                            'toggle' => 'popover',
                            // 'trigger' => 'focus',
                            'placement' => 'left',
                            'title' => '<b>' . Yii::t('frontend_views_map_connectionForm','Link out to {object}', [
                                    'object' => $provider->connection1->end0->name
                                ])  . '</b>',
                            'html' => true,
                            'content' => $content,
                        ],
                    ]);
                } else {
                    return Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} New', [
                        'icon' => FA::icon('plus'),
                    ]), [
                        'data' => [
                            'toggle' => 'modal',
                            'target' => '#connectionForm',
                            'end' => $provider->id,
                            'mode' => 'new',
                        ]
                    ]);
                }
            }
        ],
        [
            'label' => Yii::t('frontend_views_map_objectProvider','Type'),
            'attribute' => 'objectTypeName',
            'format' => 'raw',
            'value' => function($provider) {
                if (isset($provider->type0)) {
                    return Html::tag('a', $provider->type0->name, [
                        'role' => 'button',
                        'tabindex' => 0,
                        'class' => 'tab_position_popover',
                        'data' => [
                            'toggle' => 'popover',
                            'trigger' => 'focus',
                            'placement' => 'left',
                            'title' => '<b>' . $provider->type0->name . '</b>',
                            'html' => true,
                            'content' => '<b>Number of sockets</b>: ' . $provider->type0->sockets . '<br><b>Description:</b> ' . $provider->type0->description,
                        ],
                    ]);
                }
                return;
            }
        ],
        [
            'label' => Yii::t('frontend_views_map_objectProvider','Menu'),
            'format' => 'raw',
            'value' => function($provider) {
                return Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Edit', [
                    'icon' => FA::icon('edit'),
                ]), [
                    'data' => [
                        'toggle' => 'modal',
                        'target' => '#objectForm',
                        'object' => $provider->id,
                        'parent' => $provider->parent,
                        'mode' => 'edit',
                    ]])
                . ' ' .
                Html::tag('a', Yii::t('frontend_views_map_objectProvider','{icon} Delete', [
                    'icon' => FA::icon('trash'),
                ]), [
                    'data' => [
                        'toggle' => 'modal',
                        'target' => '#objectForm',
                        'object' => $provider->id,
                        'parent' => $provider->parent,
                        'mode' => 'delete',
                ]]);
            },
        ],
    ],
]) ?>
