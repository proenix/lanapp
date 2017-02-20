<?php
/**
* @license MIT License
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/

namespace frontend\assets;

use yii\web\AssetBundle;

/**
* SvgPanZoomAsset bundle.
*
* Uses additional svg-pan-zoom js library and hammer js library.
* Allows to use svg-pan-zoom on svg map object in frontend view _map.
* Application localy used scripts are located in frontend/js/map-controlls.js
*
* @todo Consider moving this Asset to frontend only.
* @author Piotr "Proenix" Trzepacz
*/
class SvgPanZoomAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/svg-pan-zoom/dist/';
    public $css = [];
    public $js = [
        'svg-pan-zoom.min.js',
        '/js/hammer.min.js',
        '/js/map-controlls.js',
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}
