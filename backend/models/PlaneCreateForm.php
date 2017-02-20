<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use common\models\Plane;
use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

/**
* Model for creating new plane via backend.
*
* Process adding new map file to database.
*
* @author Piotr "Proenix" Trzepacz
*/
class PlaneCreateForm extends \yii\base\Model
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
    * @var uploadedFile $imageFile Form field for file.
    */
    public $imageFile;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'unique', 'targetClass' => '\common\models\Plane', 'message' => Yii::t('backend_models_PlaneCreateForm','Plane with that name already exists.')],
            ['name', 'string', 'min' => 4, 'max' => 128],

            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 255],

            ['imageFile', 'required'],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 1024 * 10],

        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
        'name' => Yii::t('backend_views_plane_create','Plane name'),
        'description' => Yii::t('backend_views_plane_create','Description'),
        ];
    }

    /**
    * Process upload of file.
    *
    * Upload file to @backend/web/uploads/maps/ with random generated name.
    * And saves data to database.
    *
    * @return boolean depending on the operation success
    */
    public function upload()
    {
        if ($this->validate()) {
            $imageName = Yii::$app->security->generateRandomString() . '.' .  $this->imageFile->extension;
            $this->imageFile->saveAs('uploads/maps/' . $imageName);

            $place = new Plane();
            $place->name = $this->name;
            $place->description = $this->description;
            $place->map = $imageName;
            return $place->save();
        } else {
            return false;
        }
    }
}
