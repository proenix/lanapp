<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use common\models\User;
use yii\base\Model;
use Yii;

/**
* Model for creating users via backend.
*
* @author Piotr "Proenix" Trzepacz
*/
class UserCreateForm extends \yii\base\Model
{
    /**
    * @var string $username Form field for username.
    */
    public $username;

    /**
    * @var string $email Form field for email.
    */
    public $email;

    /**
    * @var string $_password Keeps random generated password inside model.
    */
    private $_password;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('backend_models_UserCreateForm','This username has already in use.')],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('backend_models_UserCreateForm','This email address has already in use.')],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
        'username' => Yii::t('backend_models_UserCreateForm','Username'),
        'email' => Yii::t('backend_models_UserCreateForm','Email'),
        ];
    }

    /**
    * Creates user.
    *
    * Perform sending email to newly created user.
    *
    * @return User|null the saved model or null if saving fails
    */
    public function save()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;

            // Generate password for created user. Password lenght is 16.
            $this->_password = Yii::$app->getSecurity()->generateRandomString(16);
            $user->setPassword($this->_password);
            $user->generateAuthKey();
            $user->save(false);

            $auth = Yii::$app->authManager;
            $authorRole = $auth->getRole('viewer');
            $auth->assign($authorRole, $user->getId());

            return $this->sendEmail();
        }

        return null;
    }

    /**
    * Sends an email with a username and password.
    *
    * @return boolean whether the email was send
    */
    public function sendEmail()
    {
        if (!$this->_password) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'userCreated-html', 'text' => 'userCreated-text'],
                ['password' => $this->_password]
            )
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' support'])
            ->setTo($this->email)
            ->setSubject(Yii::t('backend_models_UserCreateForm','Your access to {appname}',[
                'appname' => \Yii::$app->name,
            ]))
            ->send();
    }
}
