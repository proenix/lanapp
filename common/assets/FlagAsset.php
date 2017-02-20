<?php
/**
* @license MIT License
* @copyright Flag Icons by GoSquared (http://www.gosquared.com/) AssetBundle: (c) 2016 Piotr Trzepacz
*/

namespace common\assets;

use yii\web\AssetBundle;

/**
 * FlagAsset bundle.
 * Produce path to flag file of selected size and country.
 */
class FlagAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/flags/flags-iso/flat/';
    public $css = [];
    public $js = [];
    public $depends = [];

    const SIZE_16 = 16;
    const SIZE_24 = 24;
    const SIZE_32 = 32;
    const SIZE_48 = 48;
    const SIZE_64 = 64;

    /**
    * Returns image tag with link to flag file.
    *
    * @param string $lang Language shortcut.
    * @param int $size Size of icon.
    * @return string
    */
    public function getFlag($lang, $size = self::SIZE_24)
    {
        $link = '/';
        switch ($size) {
            case self::SIZE_16 :
                $link .= '16/';
                break;
            case self::SIZE_24 :
                $link .= '24/';
                break;
            case self::SIZE_32 :
                $link .= '32/';
                break;
            case self::SIZE_48 :
                $link .= '48/';
                break;
            case self::SIZE_64 :
                $link .= '64/';
                break;
            default :
                $link .= '16/';
                break;
        }
        switch ($lang) {
            case 'pl' :
                $link .= 'PL.png';
                break;
            case 'en' :
                $link .= 'GB.png';
                break;
            default :
                $link .= '_unknown.png';
                break;
        }
        return $link;
    }
}
