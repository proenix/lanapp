<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Type;
use common\models\Object;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
* Model for editing type via backend.
*
* Does not process sockets number editing.
*
* @author Piotr "Proenix" Trzepacz
*/
class TypeEditForm extends \yii\base\Model
{
    /**
    * @var string $name Form field for type name.
    */
    public $name;

    /**
    * @var string $name Form field for type description.
    */
    public $description;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'unique'],
            ['name', 'string', 'min' => 4, 'max' => 128],

            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('backend_models_TypeEditForm','Name'),
            'description' => Yii::t('backend_models_TypeEditForm','Description'),
        ];
    }
    /**
    * Save device type edit.
    *
    * @return boolean If saving is ok
    */
    public function save()
    {
        $this->save();
    }
}
