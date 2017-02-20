<?php
/**
* @license MIT License
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/

namespace common\assets;

use yii\web\AssetBundle;

/**
* Uploaded maps AssetBundle.
* Allows to publish maps uploaded to backedn in frontend.
*
* @author Piotr "Proenix" Trzepacz
*/
class PlaneAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@backend/web/uploads/maps/';

    public $css = [];
    public $js = [
    ];
    public $depends = [];
}
