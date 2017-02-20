<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use common\models\Group;
use common\models\Object;
use common\models\Position;
use common\models\Type;
use yii\web\BadRequestHttpException;

/**
* EditGroupModel class.
*
* Used for processing actions like adding, editing and deleting [[\common\models\Group]].
*
* Contains scenarios for each of action type:
* * `SCENARIO_NEW` - for new Group creation.
* * `SCENARIO_EDIT` - for editing exisitng Group.
* * `SCENARIO_DELETE` - for deleting Group.
*
* **WARNING**
*
* [[gParent]], [[gType]], [[gPos]] because of using [[\kartik\widgets\Select2]] widget null value should be submited as integer of 0.
*
* @author Piotr "Proenix" Trzepacz
*/
class EditGroupModel extends \yii\base\Model
{
    /**
    * @var integer $gPos link to [[\common\models\Position]]
    */
    public $gPos;

    /**
    * @var string $gPosName name of [[\common\models\Position]] of current Group used for [[\kartik\widgets\Select2]]
    */
    public $gPosName;

    /**
    * @var integer $gGroup id of current edited object
    */
    public $gGroup;

    /**
    * @var string $gName name of group unique
    */
    public $gName;

    /**
    * @var string $gDescription description of group
    */
    public $gDescription;

    /**
    * @var integer $gParent parent of current group (null if no parent) [[\common\models\Group]]
    */
    public $gParent;

    /**
    * @var string $gParentName name of parent [[\common\models\Group]] of current Group used for [[\kartik\widgets\Select2]]
    */
    public $gParentName;

    /**
    * @var integer $gType of group-element (null if not set) [[\common\models\Object]]
    */
    public $gType;

    /**
    * @var string $gTypeName name of [[\common\models\Type]] of current Group used for [[\kartik\widgets\Select2]]
    */
    public $gTypeName;

    /**
    * @var string $status Execution status of saving model. Takes values of STATUS_SUCCESS and STATUS_ERROR for SCENARIO_NEW and SCENARIO_EDIT.
    * Exception are error messages for SCENARIO_DELETE.
    */
    public $status;

    /**
    * @var string $button Button text translated to current application language for form submitButton.
    */
    public $button;

    /**
    * @var integer $gPlane Id of plane on which group is being saved or edited. [[\common\models\Plane]]
    */
    public $gPlane;

    /**
    * @var [[\common\models\Group]] $_group
    */
    private $_group;

    /**
    * @var [[\common\models\Object]] $_object
    */
    private $_object;

    /**
    * @var [[\common\models\Type]] $_type
    */
    private $_type;

    /**
    * @var [[\common\models\Group]] $_oldAttributes Stores old data for validation purposes.
    */
    private $_oldAttributes;

    const SCENARIO_NEW = 'new';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_DELETE = 'delete';

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_NO_ACCESS = 'access_error';

    /**
    * TimestampBehavior
    * @todo Check if its needed here.
    */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
    * Scenarios for model.
    *
    * Contains definition of fields that are being validated for each scenario.
    */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_EDIT] = ['gPos', 'gGroup', 'gName', 'gParent', 'gType', 'gDescription'];
        $scenarios[self::SCENARIO_NEW] = ['gPos', 'gName', 'gParent', 'gType', 'gDescription'];
        $scenarios[self::SCENARIO_DELETE] = ['gGroup'];
        return $scenarios;
    }

    /**
    * Before validation event.
    *
    * Loads old values for edit scenario.
    *
    * Loads names of relational fields like [[gParentName]].
    *
    * Check using RBAC if user can modify data.
    *
    * @return boolean Return true if completed successfuly.
    */
    public function beforeValidate()
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Edit');
            if (!($this->_oldAttributes = Group::findById($this->gGroup))) {
                $this->status = self::STATUS_ERROR;
                return false;
            }
            $this->_group = $this->_oldAttributes;
            $this->gPosName = $this->_group->position0->name;
            if (isset($this->_group->parent0)) {
                $this->gParentName = $this->_group->parent0->name;
            }
            if (isset($this->gParent)) {
                $parent = Group::findById($this->gParent);
                if ($parent) {
                    $this->gParentName = $parent->name;
                } else {
                    $this->gParentName = Yii::t('frontend_models_EditGroupModel','Error. Parent not exist.');
                }
            } else {
                $this->gParentName = Yii::t('frontend_models_EditGroupModel','Parent not set.');
            }
            if (isset($this->_group->object0->type0)) {
                $this->gTypeName = $this->_group->object0->type0->name;
            }
        } elseif ($this->scenario == self::SCENARIO_NEW) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Save new');
            $position = Position::findById($this->gPos);
            if ($position) {
                $this->gPosName = $position->name;
            }
            $parent = Group::findById($this->gGroup);
            if ($parent) {
                $this->gParentName = $parent->name;
            } else {
                $this->gParentName = Yii::t('frontend_models_EditGroupModel','Parent not set.');
            }
            $type = Type::findById($this->gType);
            if ($type) {
                $this->gTypeName = $type->name;
            }
        }

        // Check if can manage after filling all necessary form fields.
        if (!\Yii::$app->user->can('manageLan')) {
            $this->status = self::STATUS_NO_ACCESS;
            return false;
        }
        return true;
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'gGroup' => Yii::t('frontend_models_EditGroupModel', 'Group Id'),
            'gName' => Yii::t('frontend_models_EditGroupModel', 'Group name'),
            'gDescription' => Yii::t('frontend_models_EditGroupModel', 'Group description'),
            'gPos' => Yii::t('frontend_models_EditGroupModel', 'Position on map'),
            'gParent' => Yii::t('frontend_models_EditGroupModel', 'Parent group'),
            'gType' => Yii::t('frontend_models_EditGroupModel', 'Type'),
        ];
    }

    /**
    * Define validators.
    *
    * Custom validator for:
    * * checking if [[Position]] of Group is same as parent group [[Position]] runs only if Group has parent set.
    * * checking if [[gName]] is unique if changed in SCENARIO_EDIT.
    * * Check if gParent meets requirements:
    * * * allow null gParent
    * * * does not accept being parent of self
    * * * does not allow being parent of child group (checks one deep only!)
    * * * check if parent group exists
    *
    * @todo improve checking of being parent of own child group - now only one deep
    */
    public function rules()
    {
        return [
            [['gPos','gName', 'gType'], 'required'],
            ['gPos', 'integer'],
            ['gPos', 'exist', 'skipOnError' => false, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['gPos' => 'id']],

            /**
            * Custom validator for Position is same as parent position.
            */
            ['gPos', function($attribute, $params) {
                $gPar = \common\models\Group::find()
                    ->select('position')
                    ->where(['id' => $this->gParent])
                    ->one();
                if ($this->$attribute != $gPar->position) {
                    $this->addError($attribute, Yii::t('frontend_models_EditGroupModel','Bad position.'));
                }
            }, 'when' => function($model) {
                if ($model->gParent != 0)
                    return true;
                return false;
            }],

            ['gGroup', 'required', 'on' => self::SCENARIO_EDIT],
            ['gGroup', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['gGroup' => 'id'], 'on' => self::SCENARIO_EDIT],
            ['gGroup', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['gGroup' => 'id'], 'on' => self::SCENARIO_DELETE],
            ['gGroup', 'default', 'value' => null],


            ['gName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['gName' => 'name'], 'on' => self::SCENARIO_NEW],
            ['gName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['gName' => 'name'], 'on' => self::SCENARIO_EDIT,
                'when' => function ($model, $attribute) {
                    return $model->gName !== $model->_oldAttributes->name;
            }],

            ['gDescription', 'string'],


            /**
            * Custom validator for gParent.
            */
            ['gParent', function ($attribute, $params) {
                if ($this->$attribute == 0)
                    return true;

                // check if not parent of self
                if ($this->$attribute == $this->gGroup) {
                    $this->addError($attribute, Yii::t('frontend_models_EditGroupModel','Bad parent.'));
                    return false;
                }

                // check if not child of parent.
                $gPar = \common\models\Group::find()
                    ->select('parent')
                    ->where(['parent' => $this->gGroup])
                    ->one();
                if (isset($gPar->parent) && $gPar->parent == $this->gGroup) {
                    $this->addError($attribute, Yii::t('frontend_models_EditGroupModel','Bad parent.'));
                    return false;
                }

                // check if group exists.
                $gPar = \common\models\Group::find()
                    ->where(['id' => $this->$attribute])
                    ->exists();
                if (!$gPar) {
                    $this->addError($attribute, Yii::t('frontend_models_EditGroupModel','Bad parent.'));
                }
            }, 'skipOnError' => true],

            /**
            * gType of device saved in corresponding Object
            */
            ['gType', 'exist', 'skipOnEmpty' => false, 'skipOnError' => true, 'targetClass' => '\common\models\Type', 'targetAttribute' => ['gType' => 'id']],
        ];
    }

    /**
    * Update model and related objects.
    *
    * Process update for edited values for current Group.
    *
    * If position has changed update position for all child groups and elements.
    *
    * To update position of child groups and elements [[updateChildGroup]] and [[updateChildObject]] function are used recursively.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether completed successfuly.
    */
    public function update()
    {
        $group = $this->_oldAttributes;

        $group->name = $this->gName;
        $group->description = $this->gDescription;

        // Update group child groups and so on... if needed
        if ($this->gPos != $this->_oldAttributes->position) {
            $group->position = $this->gPos;
            $this->updateChildGroup($group->id, $group->position);
        }

        // Process gParent if set to none. Convert 0 to null as Select2 widget accepts only numeric values.
        if ($this->gParent != 0) {
            $group->parent = $this->gParent;
        } else {
            $group->parent = null;
        }

        if ($this->gType != 0) {
            $object = $group->object0;
            $object->type = $this->gType;
            $object->name = $this->gName;
            $object->position = $group->position;
            if ($object->validate() && $group->validate()) {
                $object->save(false);
                $this->status = self::STATUS_SUCCESS;
                return $group->save(false);
            }
        } else {
            if ($group->validate()) {
                $this->status = self::STATUS_SUCCESS;
                return $group->save(false);
            }
        }
        $this->status = STATUS_ERROR;
        return false;
    }

    /**
    * Update position for child groups of selected group.
    *
    * **WARNING** Function runs recursively!
    *
    * [[updateChildObject]] is being fired to update
    *
    * @param integer $id Id of group which child groups should be updated.
    * @param integer $pos Id of position to update to.
    */
    protected function updateChildGroup($id, $pos)
    {
        $group = Group::findById($id);
        $childGroups = $group->childs;
        $this->updateChildObject($group->id, $pos);
        foreach ($childGroups as $child) {
            $this->updateChildGroup($child->id, $pos);
        }
        $group->position = $pos;
        $group->save(false);
    }

    /**
    * Update child objects of selected group.
    *
    * @param integer $group Id of parent group.
    * @param integer $pos Id of position to update to.
    */
    protected function updateChildObject($group, $pos)
    {
        $group = Group::findById($group);
        $objects = $group->objects0;
        foreach ($objects as $object) {
            $object->position = $pos;
            $object->save(false);
        }
    }

    /**
    * Create new Group.
    *
    * During creation process new [[\common\models\Object]] linked to Group is created.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether completed successfuly.
    */
    public function save()
    {
        $group = new Group();

        $group->name = $this->gName;
        $group->description = $this->gDescription;
        $group->position = $this->gPos;
        $group->plane = $this->gPlane;
        $group->parent = $this->gParent;
        if ($this->gParent == 0 || $this->gParent == null) {
            $group->parent = null;
        }
        if ($group->save()) {
            $object = new Object();
            $object->name = $this->gName;
            $object->description = $this->gDescription;
            $object->type = $this->gType;
            $object->group = $group->id;
            $this->gGroup = $group->id;
            $object->position = $this->gPos;
            $object->plane = $this->gPlane;
            if ($object->save()) {
                $this->status = self::STATUS_SUCCESS;
                return true;
            }
            $group->delete();
            $this->status = self::STATUS_ERROR;
        }
        return false;
    }

    /**
    * Delete current Group.
    *
    * Should be used only on scenario SCENARIO_DELETE
    *
    * Return error if child groups exists.
    *
    * Return error if any of group elements has connection defined.
    *
    * @todo Optimize connections checking. Maybe use select IN(ids of objects?)
    *
    * @return boolean Whether the delete operation succeded.
    */
    public function delete()
    {
        $group = Group::findById($this->gGroup);
        if (!$group) {
            return false;
        }

        if (Group::getChildGroupsByGroupId($this->gGroup)) {
            $this->status = Yii::t('frontend_controllers_map_actionDeleteGroup','Cannot delete group {name}. Group has sub-groups.',[
                'name' => $group->name,
            ]);
            return false;
        }

        $objects = Object::getObjectsByParentId($this->gGroup);
        foreach ($objects as $object) {
            if ($object->connection0 || $object->connection1) {
                $this->status = Yii::t('frontend_controllers_map_actionDeleteGroup','Cannot delete group {name}. Group has elements that still are connected.',[
                    'name' => $group->name,
                ]);
                return false;
            }
        }

        if ($group->delete()) {
            $this->status = Yii::t('frontend_controllers_map_actionDeleteGroup','{name} deleted successfuly.',[
                'name' => $group->name,
            ]);
            return true;
        }

        $this->status = Yii::t('frontend_models_EditGroupModel', 'An error occured while deleting group.');
        return false;
    }

    /**
    * Preprocess model fields values to use in ActiveForm.
    *
    * Propagate fileds with values for SCENARIO_EDIT and SCENARIO_NEW especially for [[\kartik\widgets\Select2]] widgets to show names of position, parent group and type.
    *
    * Buttons names are also prepared.
    *
    * @return boolean Whether completed successfuly.
    */
    public function getData()
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $this->_group = Group::findById($this->gGroup);
            $this->button = Yii::t('frontend_models_EditGroupModel','Edit');
            if ($this->_group) {
                $this->gGroup = $this->_group->id;
                $this->gName = $this->_group->name;
                $this->gDescription = $this->_group->description;
                $this->gPos = $this->_group->position;
                $this->gPosName = $this->_group->position0->name;
                $this->gParent = $this->_group->parent;
                if (isset($this->_group->parent0)) {
                    $this->gParentName = $this->_group->parent0->name;
                } else {
                    $this->gParentName = Yii::t('frontend_models_EditGroupModel','Parent not set.');
                }
                if (isset($this->_group->object0->type0)) {
                    $this->gType = $this->_group->object0->type0->id;
                    $this->gTypeName = $this->_group->object0->type0->name;
                }
                return true;
            }
        } elseif ($this->scenario == self::SCENARIO_NEW) {
            $parent = Group::findById($this->gGroup);
            $position = Position::findById($this->gPos);
            $type = Type::findById($this->gType);
            $this->button = Yii::t('frontend_models_EditGroupModel','Save new');
            if ($position) {
                $this->gPosName = $position->name;
            }
            if ($parent) {
                $this->gParent = $parent->id;
                $this->gParentName = $parent->name;
            } else {
                $this->gParentName = Yii::t('frontend_models_EditGroupModel','Parent not set.');
            }
            if ($type) {
                $this->gTypeName = $type->name;
            }
            return true;
        }
        return false;
    }
}
