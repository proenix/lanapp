<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

/**
* This is the ActiveQuery class for [[\common\models\Object]].
*
* @see [[\common\models\Object]]
*/
class ObjectQuery extends \yii\db\ActiveQuery
{
    /**
    * @inheritdoc
    * @return Object[]|array
    */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
    * @inheritdoc
    * @return Object|array|null
    */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
