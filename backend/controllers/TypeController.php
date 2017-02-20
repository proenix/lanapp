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
use common\models\Type;
use common\models\Object;
use backend\models\TypeSearch;
use backend\models\TypeCreateForm;
use backend\models\TypeEditForm;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
* Type management controller.
*
* Allows to perform actions regarding [[\common\models\Type]].
*
* @author Piotr "Proenix" Trzepacz
*/
class TypeController extends \yii\web\Controller
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
    * All operations need to be performed by user which has `manageTypes` permissions.
    */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'delete', 'edit'],
                        'matchCallback' => function() {
                            return \Yii::$app->user->can('manageTypes');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
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
    * Lists all type models.
    *
    * Main action of controller allows to see and search trough all types registered in application.
    *
    *Type search model [[TypeSearch]].
    *
    * @return mixed
    */
    public function actionIndex()
    {
        $searchModel = new TypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

       return $this->render('index', [
           'searchModel' => $searchModel,
           'dataProvider' => $dataProvider,
       ]);
    }

    /**
    * Creates a new device type.
    *
    * Newly created type will be availible to use immedietly.
    *
    * If creation is successful, the browser will be redirected to the 'index' page.
    *
    * Type [[TypeCreateForm]] model to create form.
    *
    * @return mixeds
    */
    public function actionCreate()
    {
        $model = new TypeCreateForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success',Yii::t('backend_controllers_type_create','Device type {name} created successfuly.', [
                'name' => $model->name,
            ]));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
    * Deletes an existing device type.
    *
    * If deletion is successful, the browser will be redirected to the 'index' page.
    *
    * Used device type cannot be deleted.
    *
    * @return mixed
    * @throws BadRequestHttpException if $_POST['id'] is not set
    */
    public function actionDelete()
    {
        if(isset($_POST['id'])) {
            $id = $_POST['id'];
            if (Object::getNumberOfDeviceByType($id)) {
                Yii::$app->session->setFlash('error',Yii::t('backend_controllers_type_delete','Sorry. This device type cannot be deleted. Already in use.'));
            } else {
                $this->findType($id)->delete();
                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_type_delete','Device type deleted sucessfuly.'));
            }
            return $this->redirect(['index']);
        }

        throw new BadRequestHttpException('Bad parameter.');
    }

    /**
    * Changes Type name or description.
    *
    * Does not change number of sockets!
    *
    * @param integer $id Type ID
    * @return mixed
    * @throws BadRequestHttpException if cannot save model
    * @throws NotFoundHttpException via [[TypeEditForm]] if type cannot be found.
    */
    public function actionEdit($id)
    {
        $model = new TypeEditForm();
        $model = $this->findType($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_type_edit','Device type {name} updated successfuly.',[
                    'name' => $model->name,
                ]));
                return $this->redirect(['type/index']);;
            } else {
                throw new BadRequestHttpException('Data couldn\'t be saved.');
            }
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the type model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Type ID
     * @return Type the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findType($id)
    {
        if (($model = Type::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
