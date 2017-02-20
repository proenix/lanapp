<?php
/**
* Map element with panZoom.
* Shows whole map with points of positions ontop of image in svg.
* @var Plane $plane Plane object used to get plane map filename
* @var Position[] $positionList Array of Position objects to render them ontop of a map
* @var Position $currentPosition Current position for form :)
* @var $this yii\web\View
*/
use common\assets\PlaneAsset;
use frontend\assets\SvgPanZoomAsset;
use yii\helpers\Html;
use yii\widgets\Pjax;
use rmrevin\yii\fontawesome\FA;

$planeAsset = PlaneAsset::register($this);
$svgPanZoomAsset = SvgPanZoomAsset::register($this);

// Size of elipse defined
const ELLIPSE_SIZE = 14;
?>

<div class="site-svg-map col-md-6 col-sm-12 col-xs-12" style="border: 1px solid black">
    <div class="body-content">
        <svg
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:cc="http://creativecommons.org/ns#"
            xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            xmlns:svg="http://www.w3.org/2000/svg"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            version="1.1"
            id="svg-map-object"
            viewBox="0 0 1200 800"
            height="50vh"
            width="100%">
            <defs
                id="defs4" />
                <g id="mapa">
                    <image
                        width="3000"
                        height="1500"
                        preserveAspectRatio="xMidYMid meet"
                        xlink:href="<?= $planeAsset->baseUrl . '/' . $plane->map ?>"
                        id="image4155"
                        x="0"
                        y="0" />
                </g>
                <g id="data-connections"></g>
                <g id="data">
                    <?php
                    foreach ($positionsList as $position) {
                        echo Html::tag('ellipse', null, [
                            'rx' => ELLIPSE_SIZE,
                            'ry' => ELLIPSE_SIZE,
                            'cx' => $position->pos_x,
                            'cy' => $position->pos_y,
                            'id' => "ellipse-pos-" . $position->id,
                            'style' => (isset($currentPosition) && ($position->id == $currentPosition->id))?'fill:#ff0000;stroke-width:10;stroke:#ff0000;':'fill:#ffa7a7;',
                            'class' => (isset($currentPosition) && ($position->id == $currentPosition->id))?'current':'',
                        ]);
                        $link = Html::a(FA::icon('plug'), [
                                'map/index',
                                'pos' => $position->id,
                                'tab' => 'tab_group'
                            ], [
                                'data-pjax'=> '#containerPJAX',
                                'data-pos' => $position->id,
                                'class' => 'text-info bigger-link-area'
                            ]);
                        echo Html::tag('foreignObject', $link, [
                            'x' => $position->pos_x - ELLIPSE_SIZE + 5 - 15,
                            'y' => $position->pos_y - ELLIPSE_SIZE + 5 - 15,
                            'width' => 2*ELLIPSE_SIZE + 30,
                            'height' => 2*ELLIPSE_SIZE + 30,
                            'id' => "link-pos-" . $position->id,
                        ]);
                    }
                    unset($position);
                    ?>
                </g>
                <?= YII_DEBUG ? '<g
                        id="test-stuff">
                        <ellipse
                          ry="5"
                          rx="5"
                          cy="0"
                          cx="0"
                          id="elipse01"
                          style="opacity:0.553;fill:#ff0000;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:8;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                    </g>
                    ': '' ?>
        </svg>
    </div>
</div>
<ul class="list-group custom-menu" style="display:none; position: absolute;">
    <li data-action="new" class="list-group-item"><?= Yii::t('frontend_views_map_index','Create new') ?></li>
    <li data-action="edit" class="list-group-item"><?= Yii::t('frontend_views_map_index','Edit') ?></li>
    <li data-action="delete" class="list-group-item"><?= Yii::t('frontend_views_map_index','Delete') ?></li>
</ul>
