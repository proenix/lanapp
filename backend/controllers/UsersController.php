<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use common\models\User;
use common\models\Role;
use backend\models\UserSearch;
use backend\models\UserCreateForm;
use backend\models\UserSetRoleForm;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
* User management controller.
*
* Allows to perform actions regarding [[\common\models\User]].
*
* Allows to change RBAC roles for users.
*
* @author Piotr "Proenix" Trzepacz
*/
class UsersController extends \yii\web\Controller
{
    /**
    * Init() function that extends default initialize method.
    * Sets language for user depending on user language selection, cookie.
    */
    public function init()
    {
        // If user is guest then get lang from cookie or use default.
        if (Yii::$app->user->isGuest) {
            $cookies = Yii::$app->request->cookies;
            $lang = $cookies->getValue('language', '#');
            if ($lang == '#') {
                $lang = Yii::$app->params['defaultLanguage'];
            }
        }

        // If user is loged in get lang from user option.
        if (!Yii::$app->user->isGuest) {
            $lang = Yii::$app->user->identity->lang;
        }

        Yii::$app->language = $lang;
    }

    /**
    * Default behaviors.
    * All operations need to be performed by user which has `manageUsers` permissions.
    */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'status', 'delete', 'set-role'],
                        'matchCallback' => function() {
                            return \Yii::$app->user->can('manageUsers');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'status' => ['POST'],
                ],
            ],
        ];
    }

    /**
    * @inheritdoc
    */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
    * Lists all users.
    *
    * Main action of controller allows to see and search trough all users registered in application.
    *
    *Uses search model [[UserSearch]].
    *
    * @return mixed
    */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

       return $this->render('index', [
           'searchModel' => $searchModel,
           'dataProvider' => $dataProvider,
       ]);
    }

    /**
    * Creates a new user.
    *
    * Newly created user will be automaticaly ctivated and viewer role will be set for him.
    *
    * Password will be sent to user via email.
    *
    * If creation is successful, the browser will be redirected to the 'index' page.
    *
    * Uses [[UserCreateForm]] model to create form.
    *
    * @return mixed
    */
    public function actionCreate()
    {
        $model = new UserCreateForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success',Yii::t('backend_controllers_users_create','User {user} created successfuly.', [
                'user' => $model->username,
            ]));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
    * Changes status of selected user to different state.
    *
    * Action require POST argument to be sent.
    *
    * User model is linked through [[findUser]] method.
    *
    * Status is changed to different than current.
    *
    * Status cannot be changed for self.
    *
    * Status can be set to:
    * + [[\common\models\User::STATUS_ACTIVE]]
    * + [[\common\models\User::STATUS_DELETED]]
    * @return mixed
    * @throws BadRequestHttpException if no POST argument is provided
    * @throws NotFoundHttpException via [[findUser]] method if no user is found.
    */
    public function actionStatus()
    {
        if(!isset($_POST['id'])) {
            throw new BadRequestHttpException('Bad parameter.');
        }

        $id = $_POST['id'];
        $user = $this->findUser($id);

        // Throws flash message if user tries to change his own account status.
        if ($user->id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('error',Yii::t('backend_controllers_users_status','Sorry. You cannot change that user status.'));
            return $this->redirect(['index']);
        }

        if ($user->status == User::STATUS_ACTIVE) {
            $user->status = User::STATUS_DELETED;
        } else {
            $user->status = User::STATUS_ACTIVE;
        }
        $user->save();
        Yii::$app->session->setFlash('success',Yii::t('backend_controllers_users_status','User {user} status changed to {status}.', [
            'user' => $user->username,
            'status' => (($user->status == User::STATUS_ACTIVE)?(Yii::t('common_models_user','active')):(Yii::t('common_models_user','disabled'))),
        ]));
        return $this->redirect(['index']);
    }

    /**
    * Deletes an existing user model.
    *
    * If deletion is successful, the browser will be redirected to the 'index' page.
    *
    * User cannot delete himself.
    *
    * @return mixed
    * @throws BadRequestHttpException if $_POST['id'] is not set
    */
    public function actionDelete()
    {
        if(isset($_POST['id'])) {
            $id = $_POST['id'];
            if (Yii::$app->user->id == $id) {
                Yii::$app->session->setFlash('error',Yii::t('backend_controllers_users_delete','Sorry. This user cannot be deleted.'));
            } else {
                $this->findUser($id)->delete();
                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_users_delete','User deleted sucessfuly.'));
            }
            return $this->redirect(['index']);
        }

        throw new BadRequestHttpException('Bad parameter.');
    }

    /**
    * Changes user role.
    *
    * Changes user role to one of initial roles using [[UserSetRoleForm]] model.
    *
    * Cannot change role of self.
    *
    * @param integer $id User ID
    * @return mixed
    * @throws BadRequestHttpException if cannot save model
    * @throws NotFoundHttpException via [[UserSetRoleForm]] if user cannot be found.
    */
    public function actionSetRole($id)
    {
        $roles = Role::getRoleNamesAsArray();
        $model = new UserSetRoleForm;
        $model->findUser($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_users_setRole','User {username} role updated successfuly.',[
                    'username' => $model->username,
                ]));
                return $this->redirect(['users/index']);;
            } else {
                throw new BadRequestHttpException('Data couldn\'t be saved.');
            }
        }
        return $this->render('set-role', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    /**
     * Finds the user model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id User ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUser($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
