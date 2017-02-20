<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use common\models\User;

/**
* Set default plane for user.
* @author Piotr "Proenix" Trzepacz
*/
class SetDefaultPlaneForm extends \yii\base\Model
{
    /**
    * @var string $lang Form input.
    */
    public $id;

	/**
    * Creates a form model given a lang.
    * If $lang is invalid then throw to site/index
    *
    * @param string $plane
    * @param array $config name-value pairs that will be used to initialize the object properties
    */
    public function __construct($plane, $config = [])
    {
        if (empty($plane) || !is_string($plane)) {
            Yii::$app->session->setFlash('error', Yii::t('frontend_models_setDefaultPlaneForm','Sorry, not such plane.'));
            Yii::$app->getResponse()->redirect(Yii::$app->urlManager->createUrl(['map/index']));
        }
        $this->id = $plane;
        parent::__construct($config);
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'exist', 'targetClass' => '\common\models\Plane'],
        ];
    }

    /**
    * Set default Plane for user.
    * Sets cookie.
    *
    * @return boolean if set
    */
    public function setDefaultPlane()
    {
        // get the cookie collection (yii\web\CookieCollection) from the "response" component
        $cookies = Yii::$app->response->cookies;

        // add a new cookie to the response to be sent
        $cookies->add(new \yii\web\Cookie([
            'name' => 'defaultPlane',
            'value' => $this->id,
            'expire' => time() + 86400 * 365,
        ]));

        return true;
    }
}
