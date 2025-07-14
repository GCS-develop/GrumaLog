<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Siesaconector;

/**
 * SiesaconectorSearch represents the model behind the search form of `frontend\models\Siesaconector`.
 */
class SiesaconectorSearch extends Siesaconector
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['nombre', 'url_base', 'id_compania', 'id_documento', 'nombre_documento', 'id_sistema', 'header_key', 'header_token', 'created_at', 'updated_at'], 'safe'],
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
        $query = Siesaconector::find();

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
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'url_base', $this->url_base])
            ->andFilterWhere(['like', 'id_compania', $this->id_compania])
            ->andFilterWhere(['like', 'id_documento', $this->id_documento])
            ->andFilterWhere(['like', 'nombre_documento', $this->nombre_documento])
            ->andFilterWhere(['like', 'id_sistema', $this->id_sistema])
            ->andFilterWhere(['like', 'header_key', $this->header_key])
            ->andFilterWhere(['like', 'header_token', $this->header_token]);

        return $dataProvider;
    }
}
