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
use yii\bootstrap\Alert;

/**
* EditObjectModel class.
*
* Used for processing actions like adding, editing and deleting [[\common\models\Object]]
*
* Contains scenarios for each of action type:
* * `SCENARIO_NEW` - for new Object creation.
* * `SCENARIO_EDIT` - for editing exisitng Object.
* * `SCENARIO_DELETE` - for deleting Object.
*
* **WARNING**
*
* [[oParent]] and [[oType]] are using [[\kartik\widgets\Select2]] widget.
*
* @author Piotr "Proenix" Trzepacz
*/
class EditObjectModel extends \yii\base\Model
{
    /**
    * @var string $oName Name of Object.
    */
    public $oName;

    /**
    * @var string $oDescription description of Object.
    */
    public $oDescription;

    /**
    * @var integer $oType Id of object [[\common\models\Type]]. Used by [[\kartik\widgets\Select2]] in form.
    */
    public $oType;

    /**
    * @var integer $oParent Id of parent of object [[\common\models\Group]]. Used by [[\kartik\widgets\Select2]] in form.
    */
    public $oParent;

    /**
    * @var integer $oObject Id of current Object [[\common\models\Object]].
    */
    public $oObject;

    /**
    * @var [[\common\models\Group]] $_parent Parent of Object.
    */
    private $_parent;
    /**
    * @var [[\common\models\Type]] $_type Type of Object.
    */
    private $_type;

    /**
    * @var string $oParentName name of parent [[\common\models\Group]] of current Object used for [[\kartik\widgets\Select2]]
    */
    public $oParentName;

    /*
    * @var string $gTypeName name of [[\common\models\Type]] of current Group used for [[\kartik\widgets\Select2]]
    */
    public $oTypeName;

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
    * @var [[\common\models\Object]] $_oldAttributes Stores old data for validation purposes.
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
        $scenarios[self::SCENARIO_NEW] = ['oName', 'oDescription', 'oParent', 'oType'];
        $scenarios[self::SCENARIO_EDIT] = ['oObject', 'oName', 'oDescription', 'oParent', 'oType'];
        $scenarios[self::SCENARIO_DELETE] = ['oObject'];
        return $scenarios;
    }

    /**
    * Before validation event.
    *
    * Loads old values for edit scenario.
    *
    * Loads names of related fields like [[oParentName]].
    *
    * Check using RBAC if user can modify data.
    *
    * @return boolean Return true if completed successfuly.
    */
    public function beforeValidate()
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Edit');
            if (!$this->_oldAttributes = Object::findById($this->oObject)) {
                $this->status = self::STATUS_ERROR;
                return false;
            }
        } elseif ($this->scenario == self::SCENARIO_NEW) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Save new');
        }

        // Fill necessary form fields.
        if ($this->scenario == self::SCENARIO_NEW || $this->scenario == self::SCENARIO_EDIT) {
            $this->_parent = Group::findById($this->oParent);
            $this->_type = Type::findById($this->oType);

            if ($this->_type) {
                $this->oTypeName = $this->_type->name;
            }
            if ($this->_parent) {
                $this->oParentName = $this->_parent->name;
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
            'oObject' => Yii::t('fronten_models_EditObjectModel','Object Id'),
            'oName' => Yii::t('fronten_models_EditObjectModel','Element name'),
            'oDescription' => Yii::t('fronten_models_EditObjectModel','Element description'),
            'oType' => Yii::t('fronten_models_EditObjectModel','Element type'),
            'oParent' => Yii::t('fronten_models_EditObjectModel','Element parent'),
        ];
    }

    /**
    * Define validators.
    *
    * Custom validator for:
    * * checking if [[oName]] is unique if changed in SCENARIO_EDIT
    */
    public function rules()
    {
        return [
            [['oName', 'oType', 'oParent'], 'required'],

            [['oName', 'oDescription'], 'string'],

            // Check if type exists
            ['oType', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Type', 'targetAttribute' => ['oType' => 'id']],

            // Check if parent group exists
            ['oParent', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['oParent' => 'id']],

            // Check if object exist for SCENARIO_EDIT and delete
            ['oObject', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Object', 'targetAttribute' => ['oObject' => 'id'], 'on' => self::SCENARIO_EDIT],
            ['oObject', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Object', 'targetAttribute' => ['oObject' => 'id'], 'on' => self::SCENARIO_DELETE],

            // Validate duplicated names for elements
            ['oName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Object', 'targetAttribute' => ['oName' => 'name'], 'on' => self::SCENARIO_NEW],
            ['oName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Object', 'targetAttribute' => ['oName' => 'name'], 'on' => self::SCENARIO_EDIT, 'when' => function ($model, $attribute) {
                    return $model->oName !== $model->_oldAttributes->name;
            }],
        ];
    }

    /**
    * Create new Object.
    *
    * Should be used only on scenario SCENARIO_NEW.
    *
    * Saves new freshly created object into database.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether completed successfuly.
    */
    public function save()
    {
        $object = new Object();
        $object->name = $this->oName;
        $object->description = $this->oDescription;
        $object->type = $this->oType;
        $object->parent = $this->oParent;
        $object->group = null;

        $object->position = $this->_parent->position;
        $object->plane = $this->_parent->plane;
        if ($object->save()) {
            $this->oObject = $object->id;
            $this->status = self::STATUS_SUCCESS;
            return true;
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Update Object.
    *
    * Should be used only on scenario SCENARIO_EDIT.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether the operation completed successfuly.
    */
    public function update()
    {
        $object = Object::findById($this->oObject);
        if ($object) {
            $object->name = $this->oName;
            $object->description = $this->oDescription;
            $object->type = $this->oType;
            $object->parent = $this->oParent;
            $object->group = null;

            $object->position = $this->_parent->position;
            $object->plane = $this->_parent->plane;
            if ($object->save()) {
                $this->status = self::STATUS_SUCCESS;
                return true;
            }
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Delete Object.
    *
    * Should be used only on scenario SCENARIO_DELETE.
    *
    * Can only delete objects that are not used to define Group type.
    *
    * @return boolean Whether the delete operation succeded.
    */
    public function delete()
    {
        $object = Object::findById($this->oObject);
        if ($object && ($object->group == null)) {
            if ($object->connection0 || $object->connection1) {
                // If any connection exist for object in connection table
                $this->status = Yii::t('frontend_models_EditGroupModel', 'Cannot delete element {name}. Something is still connected to it.', [
                    'name' => $object->name,
                ]);
                return false;
            } else {
                // Can be deleted
                if ($object->delete()) {
                    $this->status = Yii::t('frontend_models_EditGroupModel', '{name} deleted successfuly.',[
                        'name' => $object->name,
                    ]);
                    return true;
                }
                $this->status = Yii::t('frontend_models_EditGroupModel', 'An error occured while deleting element.');
                return false;
            }
        }
        return false;
    }

    /**
    * Preprocess model fileds values to use in ActiveForm.
    *
    * Propagate fields with values for SCENARIO_EDIT and SCENARIO_NEW especially for [[\kartik\widgets\Select2]] widget to show names of parent and type of Object.
    *
    * Buttons names are also prepared.
    *
    * @return boolean Whether completed successfuly.
    */
    public function getData()
    {
        $this->_parent = Group::findById($this->oParent);
        $object = Object::findById($this->oObject);

        if ($object) {
            $this->oName = $object->name;
            $this->oDescription = $object->description;
            $this->oParent = $object->parent;
            $this->oType = $object->type;
            $this->_type = $object->type0;
        }

        if (isset($this->_parent->name))
            $this->oParentName = $this->_parent->name;

        if (isset($this->_type->name))
            $this->oTypeName = $this->_type->name;

        if ($this->scenario == self::SCENARIO_EDIT) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Edit');
            return true;
        }
        if ($this->scenario == self::SCENARIO_NEW) {
            $this->button = Yii::t('frontend_models_EditGroupModel','Save new');
            return true;
        }
    }
}
