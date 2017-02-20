<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\InvalidParamException;
use common\models\LanguageChangeForm;
use common\models\Plane;
use common\models\Group;
use common\models\Position;
use frontend\models\GroupChildSearch;
use frontend\models\ObjectSearch;
use frontend\models\SetDefaultPlaneForm;
use frontend\models\EditGroupModel;
use frontend\models\EditObjectModel;
use frontend\models\EditPositionModel;
use frontend\models\EditConnectionModel;
use frontend\models\PlaneConnections;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\bootstrap\Alert;
use yii\web\Response;
use yii\db\Query;

/**
 * Map controller.
 *
 * Perform all logic regarding operation on Planes.
 *
 * Can be used only by users with at least role viewer.
 * All operations regarding changing data must be perfomed by user with role editor or higher.
 *
 * @author Piotr "Proenix" Trzepacz
 */
class MapController extends \yii\web\Controller
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
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index', 'set-default-plane',
                            'group-form', 'object-form', 'position-form', 'connection-form',
                            'group-list', 'object-list', 'position-list', 'type-list',
                        ],
                        'matchCallback' => function() {
                            return \Yii::$app->user->can('viewLan');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
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
    * Displays plane map and controls.
    *
    * Contains main logic of application.
    *
    * Loads selected Plane and its map. Or if no map was requested asks for choosing one.
    *
    * Process GET parameters for navigations and feeds them to search models.
    *
    * Process PJAX requests for forms which can add, edit and delete data.
    *
    * Render view using partial views and then condense it to one view.
    *
    *
    * @param $map string id of plane.
    * @return mixed
    */
    public function actionIndex($map = null)
    {
        $planesList = Plane::getAllPlanes();
        $planesList = ArrayHelper::map($planesList,'id','name');

        // If param $map not set then load from cookie.
        // If no $map found in cookie load indexNoDefault view.
        if (!isset($map)) {
            $cookies = Yii::$app->request->cookies;
            $map = $cookies->getValue('defaultPlane', '#');
            if ($map == '#') {
                return $this->render('indexNoDefault', [
                    'planesList' => $planesList,
                ]);
            }
        } else {
            try {
                $model = new SetDefaultPlaneForm($map);
            } catch (InvalidParamException $e) {
                throw new BadRequestHttpException('Unsupported request.');
            }

            if(!($model->validate() && $model->setDefaultPlane())) {
                Yii::$app->session->setFlash('error',Yii::t('frontend_controllers_map_setDefaultPlane','Sorry, request could\'t not be processed.'));
            }
        }

        // Find plane and all all positions on plane.
        $plane = $this->findPlane($map);
        $positionsList = Position::getAllPositionsByPlane($map);
        /**
        * Placeholder position
        */
        $position = 0;
        $group = 0;
        $tab = 'tab_position';

        // Loads data for GridViews processing.
        if ($pjax = Yii::$app->request->get('pos')) {
            if (is_numeric($pjax)) {
                $position = $pjax;
            }
        }
        if ($pjax = Yii::$app->request->get('group')) {
            if (is_numeric($pjax)) {
                $group = $pjax;
            }
        }
        if ($pjax = Yii::$app->request->get('tab')) {
            if (is_string($pjax)) {
                $tab = $pjax;
            }
        }

        // EditGroupModel request
        // Initialize model.
        $editGroupModel = new EditGroupModel();
        // Check if data to model was send in post request.
        if (Yii::$app->request->isAjax && Yii::$app->request->post('EditGroupModel')) {
            $groupMode = Yii::$app->request->post('save-button');
            if ($groupMode == 'edit') {
                $editGroupModel->scenario = EditGroupModel::SCENARIO_EDIT;
                $editGroupModel->load(Yii::$app->request->post());
                if ($editGroupModel->validate()) {
                    if ($editGroupModel->update()) {
                        $editGroupModel->getData();
                    }
                }
            } elseif ($groupMode == 'new') {
                $editGroupModel->scenario = EditGroupModel::SCENARIO_NEW;
                $editGroupModel->load(Yii::$app->request->post());
                $editGroupModel->gPlane = $plane->id;
                if ($editGroupModel->validate()) {
                    if ($editGroupModel->save()) {
                        $editGroupModel->scenario = $editGroupModel::SCENARIO_EDIT;
                        // if success load names.
                        $editGroupModel->getData();
                    }
                    $editGroupModel->getData();
                }
            } elseif ($groupMode == 'delete') {
                $editGroupModel->scenario = EditGroupModel::SCENARIO_DELETE;
                $editGroupModel->load(Yii::$app->request->post());
                if ($editGroupModel->validate()) {
                    $editGroupModel->delete();
                }
            }
        }

        // EditObjectModel request
        // Initiatlize model.
        $editObjectModel = new EditObjectModel();
        // Check if data to model was send in post request
        if (Yii::$app->request->isAjax && Yii::$app->request->post('EditObjectModel')) {
            $objectMode = Yii::$app->request->post('save-button');
            if ($objectMode == 'edit') {
                $editObjectModel->scenario = EditObjectModel::SCENARIO_EDIT;
                $editObjectModel->load(Yii::$app->request->post());
                if ($editObjectModel->validate()) {
                    if ($editObjectModel->update())
                        $editObjectModel->getData();

                }
            } elseif ($objectMode == 'new') {
                $editObjectModel->scenario = EditObjectModel::SCENARIO_NEW;
                $editObjectModel->load(Yii::$app->request->post());
                if ($editObjectModel->validate()) {
                    if ($editObjectModel->save()) {
                        $editObjectModel->scenario = $editObjectModel::SCENARIO_EDIT;
                        $editObjectModel->getData();
                    }
                }
            } elseif ($objectMode == 'delete') {
                $editObjectModel->scenario = EditObjectModel::SCENARIO_DELETE;
                $editObjectModel->load(Yii::$app->request->post());
                if ($editObjectModel->validate()) {
                    $editObjectModel->delete();
                }
            }
        }

        // EditPositionModel request
        // Initiatlize model.
        $editPositionModel = new EditPositionModel();
        if (Yii::$app->request->isAjax && Yii::$app->request->post('EditPositionModel')) {
            $positionMode = Yii::$app->request->post('save-button');
            if ($positionMode == 'edit') {
                $editPositionModel->scenario = EditPositionModel::SCENARIO_EDIT;
                $editPositionModel->load(Yii::$app->request->post());
                if ($editPositionModel->validate()) {
                    $editPositionModel->update();
                    $editPositionModel->getData();
                }
            } elseif ($positionMode == 'new') {
                $editPositionModel->scenario = EditPositionModel::SCENARIO_NEW;
                $editPositionModel->load(Yii::$app->request->post());
                // Read plane id from current user plane
                $editPositionModel->pPlane = $plane->id;
                if ($editPositionModel->validate()) {
                    if ($editPositionModel->save()) {
                        $editPositionModel->getData();
                    }
                }
            } elseif ($positionMode == 'move') {
                throw new BadRequestHttpException("Method not implemented.", 1);
            } elseif ($positionMode == 'delete') {
                $editPositionModel->scenario = EditPositionModel::SCENARIO_DELETE;
                $editPositionModel->load(Yii::$app->request->post());
                if ($editPositionModel->validate()) {
                    $editPositionModel->delete();
                }
            }
        }

        // EditConnectionModel request
        // Initialize model.
        $editConnectionModel = new EditConnectionModel();
        if (Yii::$app->request->isAjax && Yii::$app->request->post('EditConnectionModel')) {
            $connectionMode = Yii::$app->request->post('save-button');
            if ($connectionMode == 'edit') {
                $editConnectionModel->scenario = EditConnectionModel::SCENARIO_EDIT;
                $editConnectionModel->load(Yii::$app->request->post());
                if ($editConnectionModel->validate() && $editConnectionModel->update()) {
                    $editConnectionModel->getData();
                }
            } elseif ($connectionMode == 'new') {
                $editConnectionModel->scenario = EditConnectionModel::SCENARIO_NEW;
                $editConnectionModel->load(Yii::$app->request->post());
                if ($editConnectionModel->validate() && $editConnectionModel->save()) {
                    $editConnectionModel->scenario = EditConnectionModel::SCENARIO_EDIT;
                    $editConnectionModel->getData();
                }
            } elseif ($connectionMode == 'delete') {
                $editConnectionModel->scenario = EditConnectionModel::SCENARIO_DELETE;
                $editConnectionModel->load(Yii::$app->request->post());
                if ($editConnectionModel->validate())
                    $editConnectionModel->delete();
            }
        }


        // Current position clicked
        $currentPosition = Position::findById($position);
        $currentGroup = Group::findById($group);

        // Find groupChildProvider for selected group if is set
        $groupChildSearchModel = new GroupChildSearch();
        if ($currentPosition && ($group != 0)) {
            $groupChildSearchModel->scenario = GroupChildSearch::SCENARIO_CHILD;
            $groupChildSearchModel->position = $currentPosition->id;
            $groupChildSearchModel->group = $group;
        } elseif ($currentPosition) {
            $groupChildSearchModel->scenario = GroupChildSearch::SCENARIO_POSITION;
            $groupChildSearchModel->position = $currentPosition->id;
        } else {
            $groupChildSearchModel->scenario = GroupChildSearch::SCENARIO_POSITION;
            $groupChildSearchModel->position = null;
        }

        $groupChildProvider = $groupChildSearchModel->search(Yii::$app->request->queryParams);
        $groupChildProvider->pagination->pageParam = 'group-page';
        $groupChildProvider->sort->sortParam = 'group-sort';

        // Find objectProvider for selected group if is set
        $objectSearchModel = new ObjectSearch();
        if ($currentPosition && ($group != 0)) {
            $objectSearchModel->position = $currentPosition->id;
            $objectSearchModel->group = $group;
        } else {
            $objectSearchModel->position = null;
            $objectSearchModel->group = null;
        }
        $objectProvider = $objectSearchModel->search(Yii::$app->request->queryParams);
        $objectProvider->pagination->pageParam = 'object-page';
        $objectProvider->sort->sortParam = 'object-sort';

        /**
        * Prepare array of positions connections
        */
        $planeConnections = new PlaneConnections();
        $planeConnections = json_encode($planeConnections->search($plane->id));

        /**
        * Render partial views of forms and modules.
        */
        $_groupForm = $this->renderPartial('_groupForm',[
            'editGroupModel' => $editGroupModel,
        ]);

        $_objectForm = $this->renderPartial('_objectForm',[
            'editObjectModel' => $editObjectModel,
        ]);

        $_positionForm = $this->renderPartial('_positionForm',[
            'editPositionModel' => $editPositionModel,
        ]);

        $_connectionForm = $this->renderPartial('_connectionForm',[
            'editConnectionModel' => $editConnectionModel,
        ]);

        $_groupProvider = $this->renderPartial('_groupProvider',[
            'currentGroup' => $currentGroup,
            'currentPosition' => $currentPosition,
            'groupChildProvider' => $groupChildProvider,
            'groupChildSearchModel' => $groupChildSearchModel,
        ]);

        $_objectProvider = $this->renderPartial('_objectProvider',[
            'currentGroup' => $currentGroup,
            'objectProvider' => $objectProvider,
            'objectSearchModel' => $objectSearchModel,
        ]);

        $_map = $this->renderPartial('_map',[
            'positionsList' => $positionsList,
            'plane' => $plane,
            'currentPosition' => $currentPosition,
        ]);

        return $this->render('index', [
            'plane' => $plane,
            'tab' => $tab,
            'planesList' => $planesList,
            'currentPosition' => $currentPosition,
            'currentGroup' => $currentGroup,
            '_groupForm' => $_groupForm,
            '_objectForm' => $_objectForm,
            '_connectionForm' => $_connectionForm,
            '_positionForm' => $_positionForm,
            '_groupProvider' => $_groupProvider,
            '_objectProvider' => $_objectProvider,
            'planeConnections' => $planeConnections,
            '_map' => $_map,
        ]);
    }

    /**
    * Set default plane for user in cookie.
    *
    * @throws BadRequestHttpException if id is not correct
    *
    * @return mixed
    */
    public function actionSetDefaultPlane()
    {
        if (Yii::$app->request->post()) {
            try {
                $model = new SetDefaultPlaneForm(Yii::$app->request->post('set-default-plane'));
            } catch (InvalidParamException $e) {
                throw new BadRequestHttpException('Unsupported request.');
            }

            $model->load(Yii::$app->request->post());
            if(!($model->validate() && $model->setDefaultPlane())) {
                Yii::$app->session->setFlash('error',Yii::t('frontend_controllers_map_setDefaultPlane','Sorry, request could\'t not be processed.'));
            }
            if (Yii::$app->request->referrer == null) {
                return $this->redirect(['map/index']);
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            throw new BadRequestHttpException("Not implemented.", 1);
        }
    }

    /**
     * Finds the plane based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id plane ID
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

    /**
    * AJAX response for Select2 widget for Position class
    *
    * @param string $q Search string.
    * @param integer $id Id of searched object.
    */
    public function actionPositionList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('{{%position}}')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Position::find($id)->name];
        }
        return $out;
    }

    /**
    * AJAX response for Select2 widget for Group class
    *
    * @param string $q Search string.
    * @param integer $id Id of searched object.
    */
    public function actionGroupList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('{{%group}}')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Group::find($id)->name];
        }
        // Additional no parent field.
        $out['results'] += [['id' => "0", 'text' => Yii::t('frontend_models_EditGroupModel',"No parent set.")]];
        return $out;
    }

    /**
    * AJAX response for Select2 widget for Type class
    *
    * @param string $q Search string.
    * @param integer $id Id of searched object.
    */
    public function actionTypeList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('{{%type}}')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Type::find($id)->name];
        }
        return $out;
    }

    /**
    * AJAX response for Select2 widget for Object class
    *
    * @param string $q Search string.
    * @param integer $id Id of searched object.
    */
    public function actionObjectList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('{{%object}}')
                ->where(['like', 'name', $q])
                ->andWhere(['group' => null])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Object::find($id)->name];
        }
        return $out;
    }

    /**
    * AJAX response to propagate Group edit and new form with data.
    *
    * @return mixed Html form
    */
    public function actionGroupForm() {
        if (Yii::$app->request->isAjax) {
            $model = new EditGroupModel();
            $model->gGroup = Yii::$app->request->post('gGroup');
            $model->gPos = Yii::$app->request->post('gPos');
            if(Yii::$app->request->post('mode') == 'edit')
                $model->scenario = $model::SCENARIO_EDIT;
            if(Yii::$app->request->post('mode') == 'new')
                $model->scenario = $model::SCENARIO_NEW;
            $model->getData();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }

    /**
    * AJAX response to propagate Object edit and new form with data.
    *
    * @return mixed Html form
    */
    public function actionObjectForm() {
        if (Yii::$app->request->isAjax) {
            $model = new EditObjectModel();
            $model->oObject = Yii::$app->request->post('oObject');
            $model->oParent = Yii::$app->request->post('oParent');
            if(Yii::$app->request->post('mode') == 'edit')
                $model->scenario = $model::SCENARIO_EDIT;
            if(Yii::$app->request->post('mode') == 'new')
                $model->scenario = $model::SCENARIO_NEW;
            $model->getData();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }

    /**
    * AJAX response to propagate Connection edit and new form with data.
    *
    * @return mixed Html form
    */
    public function actionConnectionForm() {
        if (Yii::$app->request->isAjax) {
            $model = new EditConnectionModel();
            if(Yii::$app->request->post('mode') == 'edit') {
                $model->cConnection = Yii::$app->request->post('cConnection');
                $model->scenario = $model::SCENARIO_EDIT;
            }
            if(Yii::$app->request->post('mode') == 'new') {
                $model->cStart = Yii::$app->request->post('cStart');
                $model->cEnd = Yii::$app->request->post('cEnd');
                $model->scenario = $model::SCENARIO_NEW;
            }
            $model->getData();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }

    /**
    * AJAX response to propagate Position edit and new form with data.
    *
    * @return mixed Html form
    */
    public function actionPositionForm() {
        if (Yii::$app->request->isAjax) {
            $model = new EditPositionModel();
            $model->pPosition = Yii::$app->request->post('pPosition');
            $model->getData();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }
}
