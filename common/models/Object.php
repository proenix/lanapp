<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* Model class for table "object".
*
* @property integer $id
* @property integer $plane
* @property integer $parent
* @property integer $position
* @property integer $type
* @property integer $group
* @property string $name
* @property string $description
*
* @property Connection $connection0
* @property Connection $connection1
* @property Group $group0
* @property Group $parent0
* @property Position $position0
* @property Plane $plane0
* @property Type $type0
*/
class Object extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%object}}';
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
            [['plane', 'parent', 'position', 'type', 'group'], 'integer'],
            [['position', 'name'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['group'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group' => 'id']],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['parent' => 'id']],
            [['position'], 'exist', 'skipOnError' => true, 'targetClass' => Position::className(), 'targetAttribute' => ['position' => 'id']],
            [['plane'], 'exist', 'skipOnError' => true, 'targetClass' => Plane::className(), 'targetAttribute' => ['plane' => 'id']],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => Type::className(), 'targetAttribute' => ['type' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_object', 'ID'),
            'plane' => Yii::t('common_models_object', 'Plane'),
            'parent' => Yii::t('common_models_object', 'Parent'),
            'position' => Yii::t('common_models_object', 'Position'),
            'type' => Yii::t('common_models_object', 'Type'),
            'group' => Yii::t('common_models_object', 'Group'),
            'name' => Yii::t('common_models_object', 'Name'),
            'description' => Yii::t('common_models_object', 'Description'),
        ];
    }

    /**
    * Get group of object (aka type of device).
    * @return \yii\db\ActiveQuery
    */
    public function getGroup0()
    {
        return $this->hasOne(Group::className(), ['id' => 'group']);
    }

    /**
    * Get parent group of object.
    * @return \yii\db\ActiveQuery
    */
    public function getParent0()
    {
        return $this->hasOne(Group::className(), ['id' => 'parent']);
    }

    /**
    * Get position of object.
    * @return \yii\db\ActiveQuery
    */
    public function getPosition0()
    {
        return $this->hasOne(Position::className(), ['id' => 'position']);
    }

    /**
    * Get plane of object.
    * @return \yii\db\ActiveQuery
    */
    public function getPlane0()
    {
        return $this->hasOne(Plane::className(), ['id' => 'plane']);
    }

    /**
    * Get type of object.
    * @return \yii\db\ActiveQuery
    */
    public function getType0()
    {
        return $this->hasOne(Type::className(), ['id' => 'type']);
    }

    /**
    * @inheritdoc
    * @return ObjectQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new ObjectQuery(get_called_class());
    }

    /**
    * Number of devices of selected type.
    * @param integer $type id of device type
    *
    * @return integer
    */
    public static function getNumberOfDeviceByType($type)
    {
        return static::find()->where(['type' => $type])->count();
    }

    /**
    * Finds object based on object group id.
    * @return Object
    */
    public static function getObjectByGroupId($id)
    {
        return static::find()->where(['group' => $id])->one();
    }

    /**
    * Finds object based on object parent id.
    * @return Object[]
    */
    public static function getObjectsByParentId($id)
    {
        return static::findAll(['parent' => $id]);
    }

    /**
    * Counts total number of objects that have parent of selected id.
    * @param integer $id Id of parent Group.
    * @return integer
    */
    public static function countObjectsByParentId($id)
    {
        return static::find()->where(['parent' => $id])->count();
    }

    /**
    * Get object by id.
    * @return Object|null
    */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
    * Get one of the ends of connection. (Connection IN)
    * @return \yii\db\ActiveQuery
    */
    public function getConnection0()
    {
        return $this->hasOne(Connection::className(), ['start' => 'id'])
            ->from(Connection::tableName() . ' con1');
    }

    /**
    * Get second of the ends of connection. (Connection OUT)
    * @return \yii\db\ActiveQuery
    */
    public function getConnection1()
    {
        return $this->hasOne(Connection::className(), ['end' => 'id'])
            ->from(Connection::tableName() . ' con2');
    }
}
