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
 * GroupOnPositionSearch represents the model behind the search for [[\common\models\Group]]s in selected Position.
 *
 * @author Piotr "Proenix" Trzepacz
 */
class GroupOnPositionSearch extends \common\models\Group
{
    /**
    * @var integer Current position for serach filter.
    */
    public $position;

    public $objectTypeName;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['position'], 'exist', 'skipOnError' => true, 'targetClass' => '\common\models\Position', 'targetAttribute' => ['position' => 'id']],
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
        return Model::scenarios();
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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andWhere(['group.position' => $this->position])
            ->andWhere(['group.parent' => null])
            ->andFilterWhere(['like', 'group.name', $this->name])
            ->andFilterWhere(['like', 'group.description', $this->description])
            ->andFilterWhere(['like', 'type.name', $this->objectTypeName]);

        return $dataProvider;
    }
}
