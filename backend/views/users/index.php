<?php
/* User Search View */
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\UserSearch */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;
use common\models\Role;
use rmrevin\yii\fontawesome\FA;

$this->title = Yii::t('backend_views_users_index','Users');
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('backend_views_users_index','Create User'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'username',
                'email:email',
                [
                    'attribute' => 'status',
                    'value' => function($provider) {
                        if ($provider->status == User::STATUS_DELETED)
                            return Yii::t('common_models_user','disabled');
                        if ($provider->status == User::STATUS_ACTIVE)
                            return Yii::t('common_models_user','active');
                    },
                    'filter' => Html::activeDropDownList($searchModel, 'status',
                        array(
                            User::STATUS_ACTIVE => Yii::t('common_models_user',"active"),
                            User::STATUS_DELETED => Yii::t('common_models_user',"disabled"),
                        ),
                        ['class'=>'form-control','prompt' => Yii::t('backend_views_users_index','All')]
                    ),
                ],
                [
                    'attribute' => 'created_at',
                    'value' => function($provider) {
                        return \Yii::$app->formatter->asDateTime($provider->created_at, 'medium');
                    },
                    'filter' => '',
                    'format' => 'html',
                ],
                [
                    'attribute' => 'updated_at',
                    'value' => function($provider) {
                        return \Yii::$app->formatter->asDateTime($provider->updated_at, 'medium');
                    },
                    'filter' => '',
                    'format' => 'html',
                ],
                [
                    'label' => Yii::t('backend_views_users_index','Roles'),
                    'attribute' => 'roleAssignment',
                    'value' => 'roleAssignment.item_name',
                    'filter' => Html::activeDropDownList($searchModel, 'roleAssignment',
                        Role::getRoleNamesAsArray(),
                        ['class'=>'form-control','prompt' => Yii::t('backend_views_users_index','All')]
                    ),
                ],
                [
                    'label' => Yii::t('backend_views_users_index','Actions'),
                    'value' => function($provider) {
                        return Html::a(Yii::t('backend_views_users_index','{icon} Lock/Unlock', [
                            'icon' => FA::icon('unlock-alt'),
                        ]),
                            ['status'],[
                                'data' => [
                                   'method' => 'post',
                                   'confirm' => Yii::t('backend_views_users_index','Do you really want to change {username} account status?',[
                                       'username' => $provider->username,
                                   ]),
                                   'params' => [
                                       'id' => $provider->id
                                   ],
                               ],
                            ]) .
                        '  ' .
                        Html::a(Yii::t('backend_views_users_index','{icon} Set role',[
                                'icon' => FA::icon('users'),
                            ]),
                            ['set-role', 'id' => $provider->id]) .
                        '  ' .
                        Html::a(Yii::t('backend_views_users_index','{icon} Delete', [
                            'icon' => FA::icon('trash'),
                        ]),
                            ['delete'],[
                            'data' => [
                               'method' => 'post',
                               'confirm' => Yii::t('backend_views_users_index','Do you realy want to delete user {username}?',[
                                   'username' => $provider->username,
                               ]),
                               'params' => [
                                   'id' => $provider->id
                               ],
                            ],
                        ]);
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>
    <?php Pjax::end(); ?></div>
