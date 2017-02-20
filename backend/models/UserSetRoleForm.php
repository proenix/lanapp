<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
* Model for setting user role via backend.
*
* @author Piotr "Proenix" Trzepacz
*/
class UserSetRoleForm extends \yii\base\Model
{
    /**
    * @var string $role Form field for role.
    */
    public $role;

    /**
    * @var /common/models/User
    */
    private $_user;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['role'],'string'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'role' => Yii::t('backend_models_UserSetRoleForm','Role'),
        ];
    }
    /**
    * Set user role.
    *
    * @return boolean If saving is ok
    */
    public function save()
    {
        $auth = Yii::$app->authManager;
        $authorRole = $auth->getRole($this->role);
        if ($auth->revokeAll($this->_user->id) && $auth->assign($authorRole, $this->_user->id)) {
            return true;
        }
        return false;
    }

    /**
    * Find user model.
    *
    * @param integer $id User ID
    * @return boolean if user is found correctly
    * @throws NotFoundHttpException if no user is found.
    * @throws BadRequestHttpException if user tries to change role of himself.
    */
    public function findUser($id)
    {
        if (!isset($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if (($this->_user = User::findIdentity($id)) == null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($this->_user->id == Yii::$app->user->id) {
            throw new BadRequestHttpException('Cannot change self role.');
        }

        $this->role = $this->_user->roleAssignment->item_name;
        return true;
    }

    /**
    * Getter method that gets username of found user.
    *
    * @return string username of user
    */
    public function getUsername()
    {
        return $this->_user->username;
    }
}
