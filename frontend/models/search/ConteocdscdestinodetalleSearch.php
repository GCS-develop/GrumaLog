<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteocdscdestinodetalle;

/**
 * ConteocdscdestinodetalleSearch represents the model behind the search form of `frontend\models\Conteocdscdestinodetalle`.
 */
class ConteocdscdestinodetalleSearch extends Conteocdscdestinodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idConteocdscdestino', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'created_at', 'updated_at'], 'safe'],
            [['totalUnidades'], 'number'],
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
    public function search($params, $idconteodestino = null)
    {
        $query = Conteocdscdestinodetalle::find();

        $query->andFilterWhere([
            'idConteocdscdestino' => $idconteodestino,
        ]);

        $query->andFilterWhere(['>', 'totalUnidades', 0]);

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
            'idConteocdscdestino' => $this->idConteocdscdestino,
            'idItem' => $this->idItem,
            'totalUnidades' => $this->totalUnidades,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        return $dataProvider;
    }
}
