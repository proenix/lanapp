<?php
/* Type Search View */
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\TypeSearch */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Type;
use common\models\Object;
use rmrevin\yii\fontawesome\FA;

$this->title = Yii::t('backend_views_type_index','Types of devices');
?>
<div class="type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('backend_views_type_index','Create device type'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'name',
                'description',
                'sockets',
                [
                    'label' => Yii::t('backend_views_type_index','Number of devices'),
                    'value' => function($provider) {
                        return Object::getNumberOfDeviceByType($provider->id);
                    }
                ],
                [
                    'label' => Yii::t('backend_views_type_index','Actions'),
                    'value' => function($provider) {
                        return
                        Html::a(Yii::t('backend_views_type_index','{icon} Edit',[
                                'icon' => FA::icon('edit'),
                            ]),
                            ['edit', 'id' => $provider->id]) .
                        '  ' .
                        Html::a(Yii::t('backend_views_type_index','{icon} Delete', [
                            'icon' => FA::icon('trash'),
                        ]),
                            ['delete'],[
                            'data' => [
                               'method' => 'post',
                               'confirm' => Yii::t('backend_views_type_index','Do you realy want to delete device type {name}?',[
                                   'name' => $provider->name,
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
