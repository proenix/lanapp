<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Connection;

/**
 * PlaneConnections returns pairs of connected positions.
 *
 * @author Piotr "Proenix" Trzepacz
 */
class PlaneConnections extends \common\models\Object
{
    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
    * Creates array fo data with search query applied
    *
    * @param $id Id of plane to be searched through.
    * @return array
    */
    public function search($id)
    {
        $query = Connection::find(['plane' => $id]);
        $query->joinWith('end0');
        $query->joinWith('end1');
        $query->asArray();
        $data = $query->all();
        $posTable = [];

        foreach ($data as $row) {
            $pos1 = $row['end0']['position'];
            $pos2 = $row['end1']['position'];
            if ($pos1 > $pos2) {
                $posTable[] = [$pos2, $pos1];
            } else {
                $posTable[] = [$pos1, $pos2];
            }
        }

        return array_unique($posTable, SORT_REGULAR);
    }
}
