<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* Model class for table "group".
* Stored groups (aka devices) and they structure in database.
*
* @property integer $id
* @property integer $plane
* @property integer $parent
* @property integer $position
* @property string $name
* @property string $description
*
* @property Group $parent0
* @property Group[] $childs
* @property Position $position0
* @property Plane $plane0
* @property Object $object0
* @property Object[] $objects0
*/
class Group extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%group}}';
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
            [['plane', 'parent', 'position'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['parent' => 'id']],
            [['position'], 'exist', 'skipOnError' => true, 'targetClass' => Position::className(), 'targetAttribute' => ['position' => 'id']],
            [['plane'], 'exist', 'skipOnError' => true, 'targetClass' => Plane::className(), 'targetAttribute' => ['plane' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_group', 'ID'),
            'plane' => Yii::t('common_models_group', 'Plane'),
            'parent' => Yii::t('common_models_group', 'Parent'),
            'position' => Yii::t('common_models_group', 'Position'),
            'name' => Yii::t('common_models_group', 'Name'),
            'description' => Yii::t('common_models_group', 'Description'),
        ];
    }

    /**
    * BeforeDelete event.
    * Fired before deleting group. Deletes object related to group and all of group objects.
    */
    public function beforeDelete(){
        if ($this->object0) {
            $this->object0->delete();
        }
        foreach($this->objects0 as $c)
            $c->delete();
        return parent::beforeDelete();
    }


    /**
    * Return parent of selected group.
    * @return \yii\db\ActiveQuery
    */
    public function getParent0()
    {
        return $this->hasOne(Group::className(), ['id' => 'parent'])
            ->from(Group::tableName() . ' parent1');
    }

    /**
    * Get childrens of group.
    * @return \yii\db\ActiveQuery
    */
    public function getChilds()
    {
        return $this->hasMany(Group::className(), ['parent' => 'id'])
            ->from(Group::tableName() . ' childs1');
    }

    /**
    * Get position of a group.
    * @return \yii\db\ActiveQuery
    */
    public function getPosition0()
    {
        return $this->hasOne(Position::className(), ['id' => 'position']);
    }

    /**
    * Get plane of a group.
    * @return \yii\db\ActiveQuery
    */
    public function getPlane0()
    {
        return $this->hasOne(Plane::className(), ['id' => 'plane']);
    }

    /**
    * Get element which defines group as device eg. switch.
    * @return \yii\db\ActiveQuery
    */
    public function getObject0()
    {
        return $this->hasOne(Object::className(), ['group' => 'id']);
    }

    /**
    * Get all objects that are child of group.
    * @return \yii\db\ActiveQuery
    */
    public function getObjects0()
    {
        return $this->hasMany(Object::className(), ['parent' => 'id']);
    }

    /**
    * @inheritdoc
    * @return GroupQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new GroupQuery(get_called_class());
    }

    /**
    * Groups that are located in selected Position.
    * @return Group
    */
    public static function getGroupsByPosition($position)
    {
        return static::findAll(['position' => $position]);
    }

    /**
    * Find all groups that are child of selected Group.
    * @return Group[]
    */
    public static function getChildGroupsByGroupId($id)
    {
        return static::findAll(['parent' => $id]);
    }

    /**
    * Find group that has provided id.
    * @return Group
    */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }
}
