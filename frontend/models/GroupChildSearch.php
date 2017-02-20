<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Group;

/**
 * GroupSearch represents the model behind the search for [[\common\models\Group]]
 * @author Piotr "Proenix" Trzepacz
 */
class GroupChildSearch extends \common\models\Group
{
    const SCENARIO_POSITION = 'position';
    const SCENARIO_CHILD = 'child';

    /**
    * @var integer Current position for serach filter.
    */
    public $position;

    public $group;

    public $objectTypeName;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['position'], 'exist', 'skipOnError' => false, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['position' => 'id']],
            [['group'], 'exist', 'skipOnError' => false, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['group' => 'id'], 'on' => self::SCENARIO_CHILD],
            ['objectTypeName', 'default', 'value' => null],
            ['position', 'default', 'value' => 0],
            [['name', 'description', 'objectTypeName'], 'safe'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_POSITION] = ['position', 'objectTypeName', 'name', 'description'];
        $scenarios[self::SCENARIO_CHILD] = ['position', 'group', 'objectTypeName', 'name', 'description'];
        return $scenarios;
    }

    /**
    * Creates data provider instance with search query applied
    *
    * @param array $params
    * @return ActiveDataProvider
    */
    public function search($params)
    {
        $query = Group::find();
        $query->joinWith(['object0', 'object0.type0']);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'name',
                'description',
                'objectTypeName' => [
                    'asc' => ['type.name' => SORT_ASC],
                    'desc' => ['type.name' => SORT_DESC],
                    'label' => 'Type',
                    'default' => SORT_ASC
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // FOR SCENARIO_POSITION
        if ($this->scenario == self::SCENARIO_POSITION) {
            $query->andWhere(['group.position' => $this->position]);
            $query->andWhere(['group.parent' => null]);
            $query->andFilterWhere([
                'id' => $this->id,
            ]);
        };

        // FOR SCENARIO_CHILD
        if ($this->scenario == self::SCENARIO_CHILD) {
            $query->andFilterWhere([
                'group.parent' => $this->group,
            ]);
            $query->andWhere(['group.position' => $this->position]);
        };

        $query->andFilterWhere(['like', 'group.name', $this->name])
            ->andFilterWhere(['like', 'group.description', $this->description])
            ->andFilterWhere(['like', 'type.name', $this->objectTypeName]);

        return $dataProvider;
    }
}
