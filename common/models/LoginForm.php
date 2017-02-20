<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
    * Define labels for model.
    */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('common_model_LoginForm','Username or Email'),
            'password' => Yii::t('common_model_LoginForm','Password'),
            'rememberMe' => Yii::t('common_model_LoginForm','Remember me next time'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('common_model_LoginForm','Incorrect username/email or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully as administrator
     */
    public function loginAdmin()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if (Yii::$app->authManager->checkAccess($user->id, 'admin')) {
                return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            }
            $this->addError('username', Yii::t('common_model_LoginForm','User does not have sufficient permission to login.'));
            return false;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        if ($this->_user === null) {
            $this->_user = User::findByEmail($this->username);
        }

        return $this->_user;
    }
}
