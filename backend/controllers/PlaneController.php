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
use yii\web\UploadedFile;
use common\models\Plane;
use common\models\Position;
use backend\models\PlaneSearch;
use backend\models\PlaneCreateForm;
use backend\models\PlaneEditForm;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
* Plane management controller.
*
* Allows to perform actions regarding [[\common\models\Plane]] management.
*
* @author Piotr "Proenix" Trzepacz
*/
class PlaneController extends \yii\web\Controller
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
    * All operations need to be performed by user which has `managePlanes` permissions.
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
                            return \Yii::$app->user->can('managePlanes');
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
    * Lists all planes.
    *
    * Main action of controller allows to see and search trough all planes registered in application.
    * Search model [[PlaneSearch]].
    *
    * @return mixed
    */
    public function actionIndex()
    {
        $searchModel = new PlaneSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
    * Creates a new plane.
    *
    * Newly created plane will be availible to use immedietly.
    *
    * If creation is successful, the browser will be redirected to the 'index' page.
    *
    * Uses [[PlaneCreateForm]] model to create form.
    *
    * @return mixeds
    */
    public function actionCreate()
    {
        $model = new PlaneCreateForm();

        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {

                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_plane_create','Plane {name} created successfuly.', [
                    'name' => $model->name,
                ]));
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
    * Deletes an existing plane.
    *
    * If deletion is successful, the browser will be redirected to the 'index' page.
    *
    * Currently used planes cannot be deleted.
    *
    * @return mixed
    * @throws BadRequestHttpException if $_POST['id'] is not set
    */
    public function actionDelete()
    {
        if(isset($_POST['id'])) {
            $id = $_POST['id'];
            if (Position::getNumberOfPositionsByPlaneId($id)) {
                Yii::$app->session->setFlash('error',Yii::t('backend_controllers_plane_delete','Sorry. This plane cannot be deleted. Already in use.'));
            } else {
                $this->findPlane($id)->delete();
                Yii::$app->session->setFlash('success',Yii::t('backend_controllers_plane_delete','Plane deleted sucessfuly.'));
            }
            return $this->redirect(['index']);
        }

        throw new BadRequestHttpException('Bad parameter.');
    }

    /**
    * Changes plane name, description or linked image.
    *
    * @param integer $id Plane ID
    * @return mixed
    * @throws BadRequestHttpException if cannot save model
    * @throws NotFoundHttpException via [[PlaneEditForm]] if plane cannot be found.
    */
    public function actionEdit($id)
    {
        $model = new PlaneEditForm();
        $model->findPlane($id);
        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success',Yii::t('backend_controllers_plane_edit','Plane {name} updated successfuly.',[
                        'name' => $model->name,
                    ]));
                    return $this->redirect(['index']);;
                } else {
                    throw new BadRequestHttpException('Data couldn\'t be saved.');
                }
            }
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the plane model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Plane ID
     * @return Plane the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findPlane($id)
    {
        if (($model = Plane::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
