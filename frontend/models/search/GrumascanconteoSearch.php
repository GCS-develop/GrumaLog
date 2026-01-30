<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Grumascanconteo;

/**
 * GrumascanconteoSearch represents the model behind the search form of `frontend\models\Grumascanconteo`.
 */
class GrumascanconteoSearch extends Grumascanconteo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idmarcacion', 'idestado', 'totalregistros', 'totalunidades', 'created_by', 'updated_by'], 'integer'],
            [['ultimoean', 'created_at', 'updated_at'], 'safe'],
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
        $query = Grumascanconteo::find();

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
            'idmarcacion' => $this->idmarcacion,
            'idestado' => $this->idestado,
            'totalregistros' => $this->totalregistros,
            'totalunidades' => $this->totalunidades,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'ultimoean', $this->ultimoean]);

        return $dataProvider;
    }
}
