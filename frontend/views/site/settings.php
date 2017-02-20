<?php
/* User profile and setting view page */
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\assets\FlagAsset;

$this->title = Yii::t('frontend_views_site_settings','Settings');
$flag = FlagAsset::register($this);
?>

<div class="site-index">
    <div class="body-content">
        <h1><?= Html::encode(Yii::t('frontend_views_site_settings','Profile details')) ?></h1>
        <p>
            <?= Yii::t('frontend_views_site_settings','Here are your account details.')?>
        </p>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Username') ?></label>
                    <div class="col-sm-8"><p class="form-control-static"><?= Yii::$app->user->identity->username ?></p></div>
                </div>
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Password') ?></label>
                    <div class="col-sm-8"><p class="form-control-static">******</p></div>
                </div>
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Email') ?></label>
                    <div class="col-sm-8"><p class="form-control-static"><?= Yii::$app->user->identity->email ?></p></div>
                </div>
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Current language') ?></label>
                    <div class="col-sm-8"><p class="form-control-static"><?= '<img src="' . Url::base(true) . $flag->baseUrl . $flag->getFlag(Yii::$app->user->identity->lang) . '">' ?></p></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Assigned roles') ?></label>
                    <div class="col-sm-8">
                        <?php foreach (\Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id) as $role): ?>
                            <p class="form-control-static"><?= Yii::t('common_roles', $role->description) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="null" class="col-sm-4 control-label"><?= Yii::t('frontend_views_site_settings','Assigned permissions') ?></label>
                    <div class="col-sm-8">
                        <?php foreach (\Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->identity->id) as $permission): ?>
                            <p class="form-control-static"><?= Yii::t('common_roles', $permission->description) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-lg-6">
                <h2><?= Yii::t('frontend_views_site_settings','Usefull links') ?></h2>
                <div class="form-group">
                    <?= Html::a(Yii::t('frontend_views_site_settings','Change password'),['site/change-password'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
