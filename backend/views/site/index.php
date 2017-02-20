<?php
/* Backend Index Navigation Page */
/* @var $this yii\web\View */

use yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
?>
<div class="site-index">

    <div class="jumbotron">
        <h1><?= Yii::t('backend_views_site_index','Welcome back {username}!',[
                'username' => Yii::$app->user->identity->username,
            ]) ?></h1>

        <p class="lead"><?= Yii::t('backend_views_site_index','What would you like to do?') ?></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-6">
                <h2><?= Yii::t('backend_views_site_index','User management') ?></h2>
                <p><?= Yii::t('backend_views_site_index','Create new, delete and change role of existing users.') ?></p>

                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} User list',[
                    'icon' => FA::icon('users')
                ]), ['users/index'], ['class' => 'btn btn-default']) ?></p>
                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} Create new user', [
                    'icon' => FA::icon('user-plus')
                ]), ['users/create'], ['class' => 'btn btn-default']) ?></p>
            </div>
            <div class="col-lg-6">
                <h2><?= Yii::t('backend_views_site_index','Device type management') ?></h2>
                <p><?= Yii::t('backend_views_site_index','Define new and remove old device types.') ?></p>

                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} Device type list',[
                    'icon' => FA::icon('plug')
                ]), ['type/index'], ['class' => 'btn btn-default']) ?></p>
                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} Create new type of device', [
                    'icon' => FA::icon('plus')
                ]), ['type/create'], ['class' => 'btn btn-default']) ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <h2><?= Yii::t('backend_views_site_index','Planes management') ?></h2>
                <p><?= Yii::t('backend_views_site_index','Define new, edit and remove planes.') ?></p>

                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} Planes list',[
                    'icon' => FA::icon('map-o')
                ]), ['plane/index'], ['class' => 'btn btn-default']) ?></p>
                <p><?= Html::a(Yii::t('backend_views_site_index','{icon} Create new plane', [
                    'icon' => FA::icon('plus')
                ]), ['plane/create'], ['class' => 'btn btn-default']) ?></p>
            </div>
        </div>
    </div>
</div>
