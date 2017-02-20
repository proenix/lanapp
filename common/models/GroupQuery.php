<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

/**
* This is the ActiveQuery class for [[\common\models\Group]].
*
* @see Group
*/
class GroupQuery extends \yii\db\ActiveQuery
{
    /**
    * @inheritdoc
    * @return Group[]|array
    */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
    * @inheritdoc
    * @return Group|array|null
    */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
