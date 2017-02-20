<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Type;

/**
 * TypeSearch represents the model behind the search form [[\common\models\Type]].
 *
 * @author Piotr "Proenix" Trzepacz
 */
class TypeSearch extends \common\models\Type
{
    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id', 'sockets'], 'integer'],
            [['name', 'description'], 'safe'],
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
    * Creates data provider instance with search query applied.
    *
    * @param array $params
    * @return ActiveDataProvider
    */
    public function search($params)
    {
        $query = Type::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'sockets', $this->sockets]);

        return $dataProvider;
    }
}
