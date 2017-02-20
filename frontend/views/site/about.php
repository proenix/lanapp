<?php
/* About project page */
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('frontend_views_about','About project');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Yii::t('frontend_views_about','Project you are now seeing was created as a part of obtaining my engineer degree. Application was developed by Piotr "Proenix" Trzepacz during the period of one and half year and hopefully will be maintained and further extended in next few years.') ?>
    </p>
    <p>
        <?= Yii::t('frontend_views_about','I would like to thank Yii Developers Team and Yii Community for creating Yii2 framework and all the extensions that could be used here. Without you this could not be created.') ?>
    </p>
    <p>
        <?= Yii::t('frontend_views_about','Application is licensed under MIT License if not stated otherwise. For more informations please check  <i>license.md</i> file in root directory of the application.') ?>
    </p>

    <p>
        <?= Yii::t('frontend_views_about','See the project page on {url}',[
            'url' => Html::a('GitHub','https://github.com/proenix/lanapp'),
        ]) ?>
    </p>
</div>
