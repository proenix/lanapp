<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form [[\common\models\User]].
 *
 * @author Piotr "Proenix" Trzepacz
 */
class UserSearch extends \common\models\User
{
    /**
    * @var string $roleAssignment Additional search field for role type.
    * Allows to search by assignment role. Connects via [[getRoleAssignment]] with [[\common\models\RoleAssignment]]
    */
    public $roleAssignment;

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id', 'created_at', 'status', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'roleAssignment'], 'safe'],
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
        $query = User::find();
        $query->joinWith(['roleAssignment']);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['roleAssignment'] = [
            'asc' => ['{{%auth_assignment}}.item_name' => SORT_ASC],
            'desc' => ['{{%auth_assignment}}.item_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', '{{%auth_assignment}}.item_name', $this->roleAssignment]);

        return $dataProvider;
    }
}
