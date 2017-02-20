<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* Model class for table "type".
* Holds all types of hardware.
*
* @property integer $id
* @property integer $sockets Number of connections that can be attached to device.
* @property string $name Name of device. Eg. Wall Socket, Switch Cisco CD-1234
* @property string $description Additional description of device. Like types of connections.
*
* @property Object[] $objects
*/
class Type extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%type}}';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['sockets'], 'integer', 'min' => 0],
            [['sockets'], 'required'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 48],
            [['name'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_position', 'ID'),
            'sockets' => Yii::t('common_models_position', 'Sockets'),
            'name' => Yii::t('common_models_position', 'Name'),
            'description' => Yii::t('common_models_position', 'Description'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
    * Gets all Objects of Type.
    * @return \yii\db\ActiveQuery
    */
    public function getObjects()
    {
        return $this->hasMany(Object::className(), ['type' => 'id']);
    }

    /**
    * Return Type for given id.
    * @param $id integer
    *
    * @return Type
    */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }
}
