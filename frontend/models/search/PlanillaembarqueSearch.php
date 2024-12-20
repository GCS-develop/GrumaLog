<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Planillaembarque;

/**
 * PlanillaembarqueSearch represents the model behind the search form of `frontend\models\Planillaembarque`.
 */
class PlanillaembarqueSearch extends Planillaembarque
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTransportadora', 'idVehiculo', 'idConductor', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['fechaDespacho', 'horaDespacho', 'placa', 'nombreConductor', 'sello', 'created_at', 'updated_at'], 'safe'],
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
        $query = Planillaembarque::find();

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
            'fechaDespacho' => $this->fechaDespacho,
            'idTransportadora' => $this->idTransportadora,
            'idVehiculo' => $this->idVehiculo,
            'idConductor' => $this->idConductor,
            'idEstado' => $this->idEstado,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'horaDespacho', $this->horaDespacho])
            ->andFilterWhere(['like', 'placa', $this->placa])
            ->andFilterWhere(['like', 'nombreConductor', $this->nombreConductor])
            ->andFilterWhere(['like', 'sello', $this->sello]);


        $query->orderBy([

            'id' => SORT_DESC,
            

        ]);

        return $dataProvider;
    }
}
