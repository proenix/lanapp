<?php
/**
* @license MIT license
*/
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ChangePasswordForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LanguageChangeForm;

/**
 * Main site controller.
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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'settings', 'change-password', 'request-password-reset', 'reset-password'],
                'rules' => [
                    [
                        'actions' => ['signup', 'request-password-reset', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'settings', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
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
     * Displays homepage.
     * Redirects to map/index as this index has no use.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('map/index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     * If signing up is not allowed does not process input and shows flash message.
     *
     * @return mixed
     */
    public function actionSignup()
    {

        $model = new SignupForm();
        if (Yii::$app->params['allowSignup']) {
            if ($model->load(Yii::$app->request->post())) {
                if ($user = $model->signup()) {
                    if (Yii::$app->getUser()->login($user)) {
                        return $this->goHome();
                    }
                }
            }
        } else {
            Yii::$app->session->setFlash('error', Yii::t('frontend_controllers_site_actionSignup','Creating new accounts is currently disabled.'));
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', Yii::t('frontend_controllers_site_actionRequestPasswordReset','Check your email for further instructions.'));

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', Yii::t('frontend_controllers_site_actionRequestPasswordReset','Sorry, we are unable to reset password for email provided.'));
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('frontend_controllers_site_actionResetPassword','New password was saved.'));

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
    * Allows to change user password.
    * Redirects user to settings page after successful change.
    *
    * @return mixed
    */
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->changePassword()) {
            Yii::$app->session->setFlash('success',Yii::t('frontend_controllers_actionChangePassword','Your password has been successfuly changed.'));
            return $this->redirect(['site/settings']);
        }

        return $this->render('changePassword', [
            'model' => $model,
        ]);
    }

    /**
    * Show all user settings.
    * Contains shortcuts for tools such as password change.
    *
    * @return mixed
    */
    public function actionSettings()
    {
        return $this->render('settings', []);
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
            Yii::$app->session->setFlash('error',Yii::t('frontend_controllers_site_language','Sorry, language unsupported.'));
        }
        if (Yii::$app->request->referrer == NULL) {
            return $this->redirect(['site/index']);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
