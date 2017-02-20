<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* Model class for table "plane".
*
* @property integer $id
* @property string $map
* @property string $name
* @property string $description
*
* @property Group[] $groups
* @property Object[] $objects
* @property Position[] $positions
*/
class Plane extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%plane}}';
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
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['map', 'description'], 'string', 'max' => 255],
            ['name', 'string', 'min' => 4, 'max' => 128],
            [['name'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_plane', 'ID'),
            'map' => Yii::t('common_models_plane', 'Map'),
            'name' => Yii::t('common_models_plane', 'Name'),
            'description' => Yii::t('common_models_plane', 'Description'),
        ];
    }

    /**
    * Get groups of Plane.
    * @return \yii\db\ActiveQuery
    */
    public function getGroups()
    {
        return $this->hasMany(Group::className(), ['plane' => 'id']);
    }

    /**
    * Get Objects of Plane.
    * @return \yii\db\ActiveQuery
    */
    public function getObjects()
    {
        return $this->hasMany(Object::className(), ['plane' => 'id']);
    }

    /**
    * Get Positions of Plane.
    * @return \yii\db\ActiveQuery
    */
    public function getPositions()
    {
        return $this->hasMany(Position::className(), ['plane' => 'id']);
    }

    /**
    * Returns list of countries in id->name schema.
    * @return array
    */
    public function getAllPlanes()
	{
		return Plane::find()->all();
	}
}
