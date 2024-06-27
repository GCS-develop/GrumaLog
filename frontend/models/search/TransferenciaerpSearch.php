<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferenciaerp;

/**
 * TransferenciaerpSearch represents the model behind the search form of `frontend\models\Transferenciaerp`.
 */
class TransferenciaerpSearch extends Transferenciaerp
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'documento', 'numeroRegistros', 'enviadoWS', 'created_by', 'updated_by'], 'integer'],
            [['descripcion', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $idconectordinamico = null)
    {
        $query = Transferenciaerp::find()->andFilterWhere(['idConectorDinamico' => $idconectordinamico]);

        // add conditions that should always apply here
        $query->orderBy(['created_at' => SORT_DESC]);

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
            'documento' => $this->documento,
            'numeroRegistros' => $this->numeroRegistros,
            'enviadoWS' => $this->enviadoWS,
            'idConectorDinamico' => $this->idConectorDinamico,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'descripcion', $this->descripcion]);


        return $dataProvider;
    }
}
