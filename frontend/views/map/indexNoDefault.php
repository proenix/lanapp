<?php
/* MapController main view if no plane is selected */
/* @var $this yii\web\View */
/* @var $planesList List of planes  */

use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use common\assets\PlaneAsset;
use yii\bootstrap\Dropdown;
use yii\bootstrap\Button;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$this->title = Yii::t('frontend_views_map_index','Choose map.');
?>
    <h2><?= Yii::t('frontend_views_map_index','Choose plane') ?></h2>

    <?= Yii::t('frontend_views_map_index','Please choose plane, selected plane will be set as default for further use.') ?>
    <br>

    <?= Html::beginForm(['/map/set-default-plane'], 'post', ['class' => 'btn-group']) ?>
        <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-default"><?= Yii::t('frontend_views_map_index','Choose plane') ?>
        <b class="caret"></b></a>
        <?php
        // Prepare data for use in Dropdown widget.
        foreach ($planesList as $id => $name) {
            $planesListLabelUrl[] = ['label' => $name, 'url' => Url::to(['map/index', 'map' => $id])];
        }
        echo Dropdown::widget([
            'items' => $planesListLabelUrl,
        ]);
        ?>
    <?= Html::endForm() ?>
