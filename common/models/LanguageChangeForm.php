<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace common\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use common\models\User;

/**
* Language changing model.
*
* @author Piotr "Proenix" Trzepacz
*/
class LanguageChangeForm extends \yii\base\Model
{
    /**
    * @var string $lang Form input.
    */
    public $lang;

    /**
    * @var /common/models/User
    */
    private $_user;

	/**
    * Creates a form model given a lang.
    * If $lang is invalid then throw to site/index
    *
    * @param string $lang
    * @param array $config name-value pairs that will be used to initialize the object properties
    */
    public function __construct($lang, $config = [])
    {
        if (empty($lang) || !is_string($lang)) {
            Yii::$app->session->setFlash('error', Yii::t('common_models_languageChangeForm','Sorry, language is not supported.'));
            Yii::$app->getResponse()->redirect(Yii::$app->urlManager->createUrl(['site/index']));
        }
        $this->lang = $lang;
        parent::__construct($config);
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['lang', 'required'],
            ['lang', 'in', 'range' => Yii::$app->params['supportedLanguages']],
        ];
    }

    /**
    * Set Language for user.
    * Sets cookie. If user is logged in also stores setting in db.
    *
    * @return boolean if set
    */
    public function setLanguage()
    {
        // get the cookie collection (yii\web\CookieCollection) from the "response" component
        $cookies = Yii::$app->response->cookies;

        // add a new cookie to the response to be sent
        $cookies->add(new \yii\web\Cookie([
            'name' => 'language',
            'value' => $this->lang,
            'expire' => time() + 86400 * 365,
        ]));

        // save preference to db if user is logged in
        if (!Yii::$app->user->isGuest) {
            $this->_user = User::findIdentity(Yii::$app->user->id);
            $this->_user->lang = $this->lang;
            $this->_user->save();
        }
        return true;
    }
}
