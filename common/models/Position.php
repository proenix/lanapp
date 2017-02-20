<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* This is the model class for table "position".
*
* @property integer $id
* @property integer $plane
* @property double $pos_x
* @property double $pos_y
* @property string $name
* @property string $description
*
* @property Group[] $groups
* @property Object[] $objects
* @property Plane $plane0
*/
class Position extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%position}}';
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
            [['plane'], 'required'],
            [['plane'], 'integer'],
            [['pos_x', 'pos_y'], 'number'],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['plane'], 'exist', 'skipOnError' => true, 'targetClass' => Plane::className(), 'targetAttribute' => ['plane' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_position', 'ID'),
            'plane' => Yii::t('common_models_position', 'Plane'),
            'pos_x' => Yii::t('common_models_position', 'Pos X'),
            'pos_y' => Yii::t('common_models_position', 'Pos Y'),
            'name' => Yii::t('common_models_position', 'Name'),
            'description' => Yii::t('common_models_position', 'Description'),
        ];
    }

    /**
    * Get Groups in being assigned to Position.
    * @return \yii\db\ActiveQuery
    */
    public function getGroups()
    {
        return $this->hasMany(Group::className(), ['position' => 'id']);
    }

    /**
    * Get Obejcts being assigned to Position.
    * @return \yii\db\ActiveQuery
    */
    public function getObjects()
    {
        return $this->hasMany(Object::className(), ['position' => 'id']);
    }

    /**
    * Get Plane of Position.
    * @return \yii\db\ActiveQuery
    */
    public function getPlane0()
    {
        return $this->hasOne(Plane::className(), ['id' => 'plane']);
    }

    /**
    * @inheritdoc
    * @return PositionQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new PositionQuery(get_called_class());
    }

    /**
    * Number of positions linked with selected plane.
    * @param integer $plane id of selected plane
    *
    * @return integer
    */
    public static function getNumberOfPositionsByPlaneId($plane)
    {
        return static::find()->where(['plane' => $plane])->count();
    }

    /**
    * Return all coordinates on selected plane.
    * @param $plane integer
    *
    * @return Position[]
    */
    public static function getAllPositionsByPlane($plane)
    {
        return static::findAll(['plane' => $plane]);
    }

    /**
    * Return Position for given id.
    * @param $id integer
    *
    * @return Position
    */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }
}
