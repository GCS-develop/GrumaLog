<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\SiesaConectorMovimientoCampo;

/**
 * SiesaconectormovimientocampoSearch represents the model behind the search form of `frontend\models\SiesaConectorMovimientoCampo`.
 */
class SiesaconectormovimientocampoSearch extends SiesaConectorMovimientoCampo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'conector_id', 'obligatorio', 'created_by', 'updated_by'], 'integer'],
            [['nombre_campo', 'alias', 'tipo_dato', 'created_at', 'updated_at'], 'safe'],
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
        $query = SiesaConectorMovimientoCampo::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'conector_id' => $this->conector_id,
            'obligatorio' => $this->obligatorio,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'nombre_campo', $this->nombre_campo])
            ->andFilterWhere(['like', 'alias', $this->alias])
            ->andFilterWhere(['like', 'tipo_dato', $this->tipo_dato]);

        return $dataProvider;
    }
}
