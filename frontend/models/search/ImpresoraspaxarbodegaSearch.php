<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Impresoraspaxarbodega;

/**
 * ImpresoraspaxarbodegaSearch represents the model behind the search form of `frontend\models\Impresoraspaxarbodega`.
 */
class ImpresoraspaxarbodegaSearch extends Impresoraspaxarbodega
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'bodega_id', 'puerto', 'created_by', 'updated_by'], 'integer'],
            [['tipo', 'ip', 'recurso', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Impresoraspaxarbodega::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '50',
            ],
        ]);

        $query->orderBy(['bodega_id' => SORT_ASC]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'bodega_id' => $this->bodega_id,
            'puerto' => $this->puerto,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'tipo', $this->tipo])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'recurso', $this->recurso]);

        return $dataProvider;
    }
}
