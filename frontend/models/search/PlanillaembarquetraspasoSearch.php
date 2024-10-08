<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Planillaembarquetraspaso;

/**
 * PlanillaembarquetraspasoSearch represents the model behind the search form of `frontend\models\Planillaembarquetraspaso`.
 */
class PlanillaembarquetraspasoSearch extends Planillaembarquetraspaso
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'idUsuarioRecibido', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['sello'], 'number'],
            [['fechaRecibido', 'created_at', 'updated_at'], 'safe'],
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
        $query = Planillaembarquetraspaso::find();

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
            'idPlanillaEmbarque' => $this->idPlanillaEmbarque,
            'idTraspaso' => $this->idTraspaso,
            'idBodegaOrigen' => $this->idBodegaOrigen,
            'idBodegaDestino' => $this->idBodegaDestino,
            'unidades' => $this->unidades,
            'unidadesEmp' => $this->unidadesEmp,
            'sello' => $this->sello,
            'fechaRecibido' => $this->fechaRecibido,
            'idUsuarioRecibido' => $this->idUsuarioRecibido,
            'idEstado' => $this->idEstado,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
