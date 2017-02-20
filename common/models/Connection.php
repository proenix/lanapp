<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
* Model class for "connection".
* Stores connections between two objects.
* Can only be created if both ends exists.
*
* @property integer $id
* @property integer $start
* @property integer $end
* @property string $description
*
* @property Object $end0
* @property Object $end1
*/
class Connection extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%connection}}';
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
            [['start', 'end'], 'required'],
            [['start', 'end'], 'integer'],
            [['description'], 'string', 'max' => 255],
            [['start'], 'unique', 'targetClass' => Connection::className(), 'message' => Yii::t('common_models_connection','Duplicated connection point.')],
            [['end'], 'unique', 'targetClass' => Connection::className(), 'message' => Yii::t('common_models_connection','Duplicated connection point.')],
            [['start'], 'exist', 'skipOnError' => false, 'targetClass' => Object::className(), 'targetAttribute' => ['start' => 'id']],
            [['end'], 'exist', 'skipOnError' => false, 'targetClass' => Object::className(), 'targetAttribute' => ['end' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common_models_connection', 'ID'),
            'start' => Yii::t('common_models_connection', 'End one'),
            'end' => Yii::t('common_models_connection', 'End second'),
            'description' => Yii::t('common_models_connection', 'Description'),
        ];
    }

    /**
    * Returns first end of connection. (Connection IN)
    * @return \yii\db\ActiveQuery
    */
    public function getEnd0()
    {
        return $this->hasOne(Object::className(), ['id' => 'start'])
            ->from(Object::tableName() . ' end0');
    }

    /**
    * Returns second end of the connection. (Connection OUT)
    * @return \yii\db\ActiveQuery
    */
    public function getEnd1()
    {
        return $this->hasOne(Object::className(), ['id' => 'end'])
            ->from(Object::tableName() . ' end1');
    }

    /**
    * Find Connection that have provided id.
    * @return Connection
    */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }
}
