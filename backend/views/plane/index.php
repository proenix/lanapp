<?php
/* Plane Search View */
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\PlaneSearch */
/* @var $planeAsset common\assets\PlaneAsset */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Plane;
use common\models\Position;
use common\assets\PlaneAsset;
use rmrevin\yii\fontawesome\FA;

$this->title = Yii::t('backend_views_plane_index','Planes');
$planeAsset = PlaneAsset::register($this);
?>
<div class="plane-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('backend_views_plane_index','Create new plane'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'name',
                'description',
                [
                    'label' => Yii::t('backend_views_plane_index','Map preview'),
                    'format' => 'html',
                    'value' => function($provider) use ($planeAsset) {
                        return
                        Html::img($planeAsset->baseUrl . '/' . $provider->map,
                        ['width' => '250px']);
                    }
                ],
                [
                    'label' => Yii::t('backend_views_plane_index','Number of defined points'),
                    'value' => function($provider) {
                        return Position::getNumberOfPositionsByPlaneId($provider->id);
                    }
                ],
                [
                    'label' => Yii::t('backend_views_plane_index','Actions'),
                    'value' => function($provider) {
                        return
                        Html::a(Yii::t('backend_views_plane_index','{icon} Edit',[
                                'icon' => FA::icon('edit'),
                            ]),
                            ['edit', 'id' => $provider->id]) .
                        '  ' .
                        Html::a(Yii::t('backend_views_plane_index','{icon} Delete', [
                            'icon' => FA::icon('trash'),
                        ]),
                            ['delete'],[
                            'data' => [
                               'method' => 'post',
                               'confirm' => Yii::t('backend_views_plane_index','Do you realy want to delete plane {name}?',[
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
