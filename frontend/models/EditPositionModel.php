<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use common\models\Position;
use yii\web\BadRequestHttpException;
use yii\bootstrap\Alert;

/**
* EditPositionModel class.
*
* Used for processing actions like adding, editing and deleting [[\common\models\Position]]
*
* Contains scenarios for each of action type:
* * `SCENARIO_NEW` - for new Position creation.
* * `SCENARIO_EDIT` - for editing exisitng Position.
* * `SCENARIO_DELETE` - for deleting Position.
*
* @author Piotr "Proenix" Trzepacz
*/
class EditPositionModel extends \yii\base\Model
{
    /**
    * @var string $pName Name of Position.
    */
    public $pName;

    /**
    * @var string $pDescription Description of Position.
    */
    public $pDescription;

    /**
    * @var integer $pPosition Id of Position.
    */
    public $pPosition;

    /**
    * @var float $pX Position of Position on x axis.
    */
    public $pX;

    /**
    * @var float $pY Position of Position on y axis.
    */
    public $pY;

    /**
    * @var integer $pPlane current plane selected and used by user. Used only for SCENARIO_NEW
    */
    public $pPlane;

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
    * @var [[Position]] $_oldAttributes Stores old data for validation purposes.
    */
    private $_oldAttributes;

    const SCENARIO_NEW = 'new';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_MOVE = 'move';
    const SCENARIO_DELETE = 'delete';

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_NO_ACCESS = 'access_error';

    /**
    * TimestampBehavior for model
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
        $scenarios[self::SCENARIO_NEW] = ['pX', 'pY', 'pName', 'pDescription', 'pPlane'];
        $scenarios[self::SCENARIO_EDIT] = ['pPosition', 'pName', 'pDescription'];
        $scenarios[self::SCENARIO_MOVE] = ['pPosition', 'pX', 'pY'];
        $scenarios[self::SCENARIO_DELETE] = ['pPosition'];
        return $scenarios;
    }

    /**
    * Before validation event.
    *
    * Loads old values for edit scenario.
    *
    * @return boolean Return true if completed successfuly.
    */
    public function beforeValidate()
    {
        if (!\Yii::$app->user->can('manageLan')) {
            $this->status = self::STATUS_NO_ACCESS;
            return false;
        }
        if ($this->scenario == self::SCENARIO_EDIT) {
            if (!$this->_oldAttributes = Position::findById($this->pPosition)) {
                $this->status = self::STATUS_ERROR;
                return false;
            }
        }
        return true;
    }

    /**
    * Define validators.
    *
    * Custom validator for:
    * * checking if [[pName]] is unique if changed in SCENARIO_EDIT
    */
    public function rules()
    {
        return [
            ['pName', 'required'],

            [['pName', 'pDescription'], 'string'],

            ['pPosition', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['pPosition' => 'id']],

            [['pX', 'pY'], 'required'],

            ['pPlane', 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Plane', 'targetAttribute' => ['pPlane' => 'id']],

            // Validate duplicated names for elements
            ['pName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['pName' => 'name'], 'on' => self::SCENARIO_NEW],

            ['pName', 'unique', 'skipOnError' => true, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['pName' => 'name'], 'on' => self::SCENARIO_EDIT, 'when' => function ($model, $attribute) {
                return $model->pName !== $model->_oldAttributes->name;
            }],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'pName' => Yii::t('frontend_models_EditPositionModel','Position name'),
            'pDescription' => Yii::t('frontend_models_EditPositionModel','Position description'),
            'pX' => Yii::t('frontend_models_EditPositionModel','Position X coordinate'),
            'pY' => Yii::t('frontend_models_EditPositionModel','Position Y coordinate'),
        ];
    }

    /**
    * Create new Position.
    *
    * Should be used only on scenario SCENARIO_NEW
    *
    * Saves new freshly created object into database.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether completed successfuly.
    */
    public function save()
    {
        $position = new Position();

        $position->name = $this->pName;
        $position->description = $this->pDescription;
        $position->pos_x = $this->pX;
        $position->pos_y = $this->pY;
        $position->plane = $this->pPlane;

        if ($position->save()) {
            $this->pPosition = $position->id;
            $this->status = self::STATUS_SUCCESS;
            return true;
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Update Position.
    *
    * Should be used only on scenario SCENARIO_EDIT.
    *
    * [[status]] is set to STATUS_SUCCESS or STATUS_ERROR depending on result.
    *
    * @return boolean Whether the operation completed successfuly.
    */
    public function update()
    {
        $position = Position::findById($this->pPosition);

        if ($position) {
            $position->name = $this->pName;
            $position->description = $this->pDescription;
            if ($position->save()) {
                $this->status = self::STATUS_SUCCESS;
                return true;
            }
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Delete current position.
    *
    * Should be used only on scenario SCENARIO_DELETE
    *
    * Can only delete position that have no groups.
    *
    * @return boolean Whether the delete operation succeded.
    */
    public function delete()
    {
        Yii::warning($this->pPosition);
        $position = Position::findById($this->pPosition);
        if ($position) {
            if ($position->groups) {
                $this->status = Yii::t('frontend_models_EditPositionModel', 'Cannot delete position {name}. Has groups defined.', [
                    'name' => $position->name,
                ]);
                return false;
            }

            if ($position->delete()) {
                $this->status = self::STATUS_SUCCESS;
                return true;
            }
        }
        $this->status = self::STATUS_ERROR;
        return false;
    }

    /**
    * Preprocess model fileds values to use in ActiveForm.
    *
    * Propagate fields with values for Form.
    *
    * @return boolean Whether completed successfuly.
    */
    public function getData()
    {
        $position = Position::findById($this->pPosition);

        if ($position) {
            $this->pX = $position->pos_x;
            $this->pY = $position->pos_y;
            $this->pName = $position->name;
            $this->pDescription = $position->description;
            return true;
        } else {
            throw new BadRequestHttpException("Error Processing Request", 500);
        }
    }
}
