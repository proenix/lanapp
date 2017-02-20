<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use common\models\Type;
use yii\base\Model;
use Yii;

/**
* Model for creating type via backend.
*
* @author Piotr "Proenix" Trzepacz
*/
class TypeCreateForm extends \yii\base\Model
{
    /**
    * @var string $name Form field for name of device.
    */
    public $name;

    /**
    * @var string $description Form field for description.
    */
    public $description;

    /**
    * @var string $description Form field for number of sockets.
    */
    public $sockets;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'unique', 'targetClass' => '\common\models\Type', 'message' => Yii::t('backend_models_TypeCreateForm','This type of device already exists.')],
            ['name', 'string', 'min' => 4, 'max' => 128],

            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 255],

            ['sockets', 'required'],
            ['sockets', 'integer', 'min' => 0, 'max' => 256],

        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
        'name' => Yii::t('backend_models_TypeCreateForm','Device type name'),
        'description' => Yii::t('backend_models_TypeCreateForm','Description'),
        'sockets' => Yii::t('backend_models_TypeCreateForm','Number of sockets'),
        ];
    }

    /**
    * Initialize method.
    *
    * Set defaultValue for sockets field.
    */
    public function init()
    {
        $this->sockets = 0;
    }

    /**
    * Saves device type in database after validation.
    *
    * @return boolean depending on operation success
    */
    public function save()
    {
        if ($this->validate()) {
            $type = new Type();
            $type->name = $this->name;
            $type->description = $this->description;
            $type->sockets = $this->sockets;

            return $type->save();
        }
        return false;
    }
}
