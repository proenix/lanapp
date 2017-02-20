<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Object;

/**
 * ObjectSearch represents the model behind the search for [[\common\models\Object]]
 * @author Piotr "Proenix" Trzepacz
 */
class ObjectSearch extends \common\models\Object
{
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
            [['group'], 'exist', 'skipOnError' => false, 'targetClass' => '\common\models\Group', 'targetAttribute' => ['group' => 'id']],
            ['objectTypeName', 'default', 'value' => null],
            ['position', 'default', 'value' => 0],
            ['group', 'default', 'value' => 0],
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
        $query = Object::find();
        $query->joinWith(['type0']);
        $query->joinWith(['connection0']);
        $query->joinWith(['connection1']);

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
            'object.parent' => $this->group,
        ]);

        $query->andWhere(['position' => $this->position])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'type.name', $this->objectTypeName]);

        return $dataProvider;
    }
}
