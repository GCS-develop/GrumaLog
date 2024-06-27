<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspaso;

/**
 * searchTraspasoSearch represents the model behind the search form of `frontend\models\Traspaso`.
 */
class searchTraspasoSearch extends Traspaso
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idCentroOperacion', 'idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'idUltimoItem', 'created_by', 'updated_by'], 'integer'],
            [['consecutivo'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
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
        $query = Traspaso::find();

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
            'idCentroOperacion' => $this->idCentroOperacion,
            'idBodegaOrigen' => $this->idBodegaOrigen,
            'idBodegaDestino' => $this->idBodegaDestino,
            'numeroCajas' => $this->numeroCajas,
            'idTipoDocumento' => $this->idTipoDocumento,
            'consecutivo' => $this->consecutivo,
            'idEstado' => $this->idEstado,
            'idUltimoItem' => $this->idUltimoItem,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
