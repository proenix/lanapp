<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use common\models\Object;
use common\models\Connection;
use yii\bootstrap\Alert;

/**
* EditConnectionModel class.
*
* Used for processing actions like adding, editing and deleting [[\common\models\Connection]]
*
* Contains scenarios for each of action type:
* * `SCENARIO_NEW` - for new Connection creation.
* * `SCENARIO_EDIT` - for editing exisitng Connection.
* * `SCENARIO_DELETE` - for deleting Connection.
*
* **WARNING**
*
* [[cStart]] and [[cEnd]] are using [[\kartik\widgets\Select2]] widget.
*
* @author Piotr "Proenix" Trzepacz
*/
class EditConnectionModel extends \yii\base\Model
{
    /**
    * @var string $cDescription description of Connection.
    */
    public $cDescription;

    /**
    * @var integer $cConnection Id of current [[\common\models\Connection]].
    */
    public $cConnection;

    /**
    * @var integer $cStart Id of [[\common\models\Group]] which is one end of connection.
    */
    public $cStart;

    /**
    * @var integer $cEnd Id of [[\common\models\Group]] which is second end of connection.
    */
    public $cEnd;

    /**
    * @var string $cStarName Name of [[cStart]] [[\common\models\Group]]. Used for [[\kartik\widgets\Select2]] widget in form.
    */
    public $cStartName;

    /**
    * @var string $cEndName Name of [[cEnd]] [[\common\models\Group]]. Used for [[\kartik\widgets\Select2]] widget in form.
    */
    public $cEndName;

    /**
    * @var string $status Execution status of saving model.
    *
    * Takes values of STATUS_SUCCESS and STATUS_ERROR for SCENARIO_NEW and SCENARIO_EDIT.
    *
    * Exception are error messages for SCENARIO_DELETE.
    */
    public $status;

    /**
    * @var string $button Button text translated to current application language for form submitButton.
    */
    public $button;

    /**
    * @var [[\common\models\Connection]] $_oldAttributes Stores old data for validation purposes.
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
        $scenarios[self::SCENARIO_NEW] = ['cStart', 'cEnd', 'cDescription'];
        $scenarios[self::SCENARIO_EDIT] = ['cStart', 'cEnd', 'cDescription', 'cConnection'];
        $scenarios[self::SCENARIO_DELETE] = ['cConnection'];
        return $scenarios;
    }

    /**
    * Before validation event.
    *
    * Loads old values for edit scenario.
    *
    * Loads names of related fields like [[cStartName]], [[cEndName]].s
    *
    * Check using RBAC if user can modify data.
    *
    * @return boolean Return true if completed successfuly.
    */
    public function beforeValidate()
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $this->button = Yii::t('frontend_views_map_connectionForm','Edit');
            $this->_oldAttributes = Connection::findById($this->cConnection);
            if (!$this->_oldAttributes) {
                Yii::warning('duppo');
                return false;
            }
        } elseif ($this->scenario == self::SCENARIO_NEW) {
            $this->button = Yii::t('frontend_views_map_connectionForm','Save');
        }

        if ($this->scenario == self::SCENARIO_NEW || $this->scenario == self::SCENARIO_EDIT) {
            $startObject = Object::findById($this->cStart);
            if ($startObject)
                $this->cStartName = $startObject->name;

            $endObject = Object::findById($this->cEnd);
            if ($endObject)
                $this->cEndName = $endObject->name;
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
            'cConnection' => Yii::t('frontend_models_EditConnectionModel','Connection Id'),
            'cDescription' => Yii::t('frontend_models_EditConnectionModel','Connection description'),
            'cStart' => Yii::t('frontend_views_map_connectionForm','Connection in from'),
            'cEnd' => Yii::t('frontend_views_map_connectionForm','Connection out to'),
        ];
    }

    /**
    * Define validators.
    *
    * Custom validator for:
    */
    public function rules()
    {
        return [
            [['cStart', 'cEnd'], 'required', 'on' => self::SCENARIO_NEW],
            [['cConnection', 'cStart', 'cEnd'], 'required', 'on' => self::SCENARIO_EDIT],
            [['cConnection'], 'required', 'on' => self::SCENARIO_DELETE],

            [['cDescription'], 'string'],

            ['cConnection', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Connection', 'targetAttribute' => ['cConnection' => 'id']],

            // Check if connection is unique for Object
            ['cStart', 'unique', 'skipOnError' => true,
                'targetClass' => '\common\models\Connection',
                'targetAttribute' => ['cStart' => 'start'],
                'message' => Yii::t('frontend_models_EditConnectionModel','Element have connection in already defined.'),
                'on' => self::SCENARIO_EDIT,
                'when' => function ($model, $attribute) {
                    return $model->cStart != $model->_oldAttributes->start;
                }],
            ['cEnd', 'unique', 'skipOnError' => true,
                'targetClass' => '\common\models\Connection',
                'targetAttribute' => ['cEnd' => 'end'],
                'message' => Yii::t('frontend_models_EditConnectionModel','Element have connection out already defined.'),
                'on' => self::SCENARIO_EDIT,
                'when' => function ($model, $attribute) {
                    return $model->cEnd != $model->_oldAttributes->end;
                }],
            ['cStart', 'unique', 'skipOnError' => true,
                'targetClass' => '\common\models\Connection',
                'targetAttribute' => ['cStart' => 'start'],
                'message' => Yii::t('frontend_models_EditConnectionModel','Element have connection in already defined.'),
                'on' => self::SCENARIO_NEW],
            ['cEnd', 'unique', 'skipOnError' => true,
                'targetClass' => '\common\models\Connection',
                'targetAttribute' => ['cEnd' => 'end'],
                'message' => Yii::t('frontend_models_EditConnectionModel','Element have connection out already defined.'),
                'on' => self::SCENARIO_NEW],
        ];
    }

    /**
    * Create new Connection.
    *
    * Should be used only on scenario SCENARIO_NEW.
    *
    * Saves new freshly created Connection into database.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether completed successfuly.
    */
    public function save()
    {
        $connection = new Connection();
        $connection->start = $this->cStart;
        $connection->end = $this->cEnd;
        $connection->description = $this->cDescription;
        if ($connection->save()) {
            $this->cConnection = $connection->id;
            $this->status = self::STATUS_SUCCESS;
            return true;
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Update Connection.
    *
    * Should be used only on scenario SCENARIO_EDIT.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether the operation completed successfuly.
    */
    public function update()
    {
        $connection = Connection::findById($this->cConnection);
        if ($connection) {
            $connection->description = $this->cDescription;
            $connection->start = $this->cStart;
            $connection->end = $this->cEnd;
            if ($connection->validate() && $connection->save()) {
                $this->status = self::STATUS_SUCCESS;
                return true;
            }
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Delete Connection.
    *
    * Should be used only on scenario SCENARIO_DELETE.
    *
    * @return boolean Whether the delete operation succeded.
    */
    public function delete()
    {
        $connection = Connection::findById($this->cConnection);
        if ($connection->delete()) {
            $this->status = Yii::t('frontend_models_EditConnectionModel', 'Connection deleted successfuly.');
            return true;
        }
        return false;
    }

    /**
    * Preprocess model fileds values to use in ActiveForm.
    *
    * Propagate fields with values for SCENARIO_EDIT and SCENARIO_NEW especially for [[\kartik\widgets\Select2]] widget to show names of Objects of Connection.
    *
    * Buttons names are also prepared.
    *
    * @return boolean Whether completed successfuly.
    */
    public function getData()
    {
        if ($this->scenario == self::SCENARIO_EDIT) {
            $connection = Connection::findById($this->cConnection);

            if (isset($connection->description))
                $this->cDescription = $connection->description;

            if (isset($connection->end0)) {
                $this->cStartName = $connection->end0->name;
                $this->cStart = $connection->end0->id;
            }

            if (isset($connection->end1)) {
                $this->cEndName = $connection->end1->name;
                $this->cEnd = $connection->end1->id;
            }

            $this->button = Yii::t('frontend_models_EditGroupModel','Edit');
            return true;
        }

        if ($this->scenario == self::SCENARIO_NEW) {
            $startObject = Object::findById($this->cStart);
            if ($startObject)
                $this->cStartName = $startObject->name;

            $endObject = Object::findById($this->cEnd);
            if ($endObject)
                $this->cEndName = $endObject->name;

            $this->button = Yii::t('frontend_models_EditGroupModel','Save new');
            return true;
        }
    }
}
