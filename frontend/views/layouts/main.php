<?php
/* Backend Main layout file */
/* @var $this \yii\web\View */
/* @var $content string */
/* @var $flag \common\assets\FlagAsset */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\assets\FlagAsset;
use common\widgets\Alert;

AppAsset::register($this);
$flag = FlagAsset::register($this);
$js = <<< 'SCRIPT'
/* To initialize BS3 tooltips set this below */
$(function () {
    $("[data-toggle='tooltip']").tooltip();
});;
/* To initialize BS3 popovers set this below */
$(function () {
    $("[data-toggle='popover']").popover();
});
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name . ' - ' . $this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => Yii::t('layout_main_menuItems','Map'), 'url' => ['/map/index']],
    ];
    if (Yii::$app->user->isGuest) {
        if (Yii::$app->params['allowSignup']) {
            $menuItems[] = ['label' => Yii::t('layout_main_menuItems','Signup'), 'url' => ['/site/signup']];
        }
        $menuItems[] = ['label' => Yii::t('layout_main_menuItems','Login'), 'url' => ['/site/login']];
    } else {
        $menuItems[] = ['label' => Yii::t('layout_main_menuItems','Settings'), 'url' => ['/site/settings']];
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                Yii::t('layout_main_menuItems','Logout') . ' (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>';
    }
    $menuItems[] = [
        'label' => '<img src="' . Url::base(true) . $flag->baseUrl . $flag->getFlag(Yii::$app->language) . '">',
        'items' => [
             '<li class="dropdown-header">'.Yii::t('layout_main_menuItems','Choose your language').'</li>',
             '<li class="divider"></li>',
             ['label' => '<img src="' . Url::base(true) . $flag->baseUrl . $flag->getFlag('en') . '"> ' . Yii::t('layout_main_menuItems','English'), 'url' => ['site/language', 'lang' => 'en']],
             ['label' => '<img src="' . Url::base(true) . $flag->baseUrl . $flag->getFlag('pl') . '"> ' . Yii::t('layout_main_menuItems','Polish'), 'url' => ['site/language', 'lang' => 'pl']],
        ],
        'dropDownOptions' => [
            'style' => 'min-width: 0;',
        ]
    ];

    echo Nav::widget([
        'encodeLabels' => false,
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Proenix 2015-<?= date('Y') ?> <?= Html::a( Yii::t('layout_main_menuItems','About project.'), Url::to(['site/about'], true)) ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
