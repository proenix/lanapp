<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\LoginForm;
use common\models\LanguageChangeForm;
use yii\web\BadRequestHttpException;

/**
 * Main pages controller.
 *
 * Backend site controller core function is to log in, log out and change display language for users.
 *
 * Availible only for users with administrator role.
 *
 * @author Piotr "Proenix" Trzepacz
 */
class SiteController extends \yii\web\Controller
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
    * All operations need to be performed by user which has `manageUsers`, `manageTypes` or `managePlanes` permissions.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'logout', 'language'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function() {
                            return (\Yii::$app->user->can('manageUsers') || \Yii::$app->user->can('manageTypes') || \Yii::$app->user->can('managePlanes'));
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
    * Landing page for backend.
    * Main function is to show shortcut links to main functions of backend.
    */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
    * Login action.
    * Allows user to log into system.
    * Only administrator users are able to log in.
    */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->loginAdmin()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
    * Logout action.
    * Allows user to log off.
    */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
    * Language change request
    *
    * @param string $lang language shortcut that's defined in params->supportedLanguages array
    * @return mixed
    * @throws BadRequestHttpException if model [[LanguageChangeForm]] could not be initialized correctly.
    */
    public function actionLanguage($lang)
    {
        try {
            $model = new LanguageChangeForm($lang);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException('Unsupported language');
        }

        $model->load(Yii::$app->request->get());
        if(!($model->validate() && $model->setLanguage())) {
            Yii::$app->session->setFlash('error',Yii::t('backend_controllers_site_language','Sorry, language unsupported.'));
        }
        if (Yii::$app->request->referrer == NULL) {
            return $this->redirect(['site/index']);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
