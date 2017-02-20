<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Plane;
use common\models\Object;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
* Model for editing plane via backend.
*
* @author Piotr "Proenix" Trzepacz
*/
class PlaneEditForm extends \yii\base\Model
{
    /**
    * @var string $name Form field for name.
    */
    public $name;

    /**
    * @var string $name Form field for description.
    */
    public $description;

    /**
    * @var uploadedFile $imageFile Form field for file.
    */
    public $imageFile;

    /**
    * @var Plane
    */
    private $_plane;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 4, 'max' => 128],
            ['name', 'unique', 'targetClass' => '\common\models\Plane', 'message' => Yii::t('backend_models_PlaneEditForm','This name is already in use.'), 'when' => function ($model, $attribute) {
                return $model->name != $model->_plane->name;
            },],

            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 255],

            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 1024 * 10],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('backend_models_PlaneEditForm','Name'),
            'description' => Yii::t('backend_models_PlaneEditForm','Description'),
            'imageFile' => Yii::t('backend_models_PlaneEditForm','Map image')
        ];
    }

    /**
    * Save plane after edit.
    * Process uploading new map file is exists.
    *
    * @return boolean If saving is ok
    */
    public function save()
    {
        if (!$this->validate()) {
            throw new BadRequestHttpException('bad validation.');
        }

        $plane = $this->_plane;
        $plane->name = $this->name;
        $plane->description = $this->description;
        if ($this->validate() && $plane->validate()) {
            if (isset($this->imageFile->extension)) {
                $imageName = Yii::$app->security->generateRandomString() . '.' .  $this->imageFile->extension;
                $this->imageFile->saveAs('uploads/maps/' . $imageName);
                $plane->map = $imageName;
            }
            return $plane->save();
        }
        return false;
    }

    /**
    * Find plane by id.
    *
    * @param integer $id Plane ID
    * @return boolean if user is found correctly
    * @throws NotFoundHttpException if no user is found.
    */
    public function findPlane($id)
    {
        if (($this->_plane = Plane::findOne($id)) !== null) {
            $this->name = $this->_plane->name;
            $this->description = $this->_plane->description;
            return true;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
