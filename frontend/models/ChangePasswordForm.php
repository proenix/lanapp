<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
* Password changing form
*/
class ChangePasswordForm extends \yii\base\Model
{
    /**
    * @var string $password_old stores user's old password
    */
    public $password_old;
    
    /**
    * @var string $password_new stores user's new password
    */
    public $password_new;

    /**
    * @var string $password_new2 stores user's new repeated password
    */
    public $password_new2;

    /**
    * @var \common\models\User
    */
    private $_user;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            // all fields are required
            [['password_old','password_new','password_new2'], 'required'],
            // all fields must meet requirement of minimum 6 chars
            [['password_old','password_new','password_new2'], 'string', 'min' => 6],
            // both new passwords must me exacly the same
            ['password_new2','compare','compareAttribute' => 'password_new', 'operator' => '==', 'message' => \Yii::t('frontend_models_ChangePasswordForm','Repeated password doesn\'t match')],
            // new password must me different than old one
            ['password_new','compare','compareAttribute' => 'password_old', 'operator' => '!=', 'message' => \Yii::t('frontend_models_ChangePasswordForm','New password should be different than old one.')],
            // password_old is validated by validatePassword
            ['password_old', 'validatePassword'],
        ];
    }

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return [
			'password_new' => \Yii::t('frontend_models_ChangePasswordForm','New password'),
            'password_new2' => \Yii::t('frontend_models_ChangePasswordForm','Repeat password'),
            'password_old' => \Yii::t('frontend_models_ChangePasswordForm','Current password'),
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
            if (!$user || !$user->validatePassword($this->password_old)) {
                $this->addError($attribute, Yii::t('frontend_models_ChangePasswordForm','Entered password doesn\'t match with current one.'));
            }
        }
    }

    /**
    * Change password.
    *
    * @return boolean if password was changed.
    */
    public function changePassword()
    {
        $user = $this->_user;

        if ($this->validate()) {
            $user->setPassword($this->password_new);
            $user->removePasswordResetToken();

            return $user->save(false);
        } else {
            return false; //thats probably unnecessary line
        }
    }

    /**
    * Finds user via [[User::findIdentity]]
    *
    * @return User|null
    */
    protected function getUser()
    {
        if ($this->_user == null) {
            $this->_user = User::findIdentity(Yii::$app->user->id);
        }
        return $this->_user;
    }
}
