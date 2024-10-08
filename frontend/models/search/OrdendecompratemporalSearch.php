<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ordendecompratemporal;

/**
 * OrdendecompratemporalSearch represents the model behind the search form of `frontend\models\Ordendecompratemporal`.
 */
class OrdendecompratemporalSearch extends Ordendecompratemporal
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idCO', 'idTipoDocumento', 'idProveedor', 'idComprador', 'idCondicionPago', 'created_by', 'updated_by'], 'integer'],
            [['fechaDocumento', 'sucursalProveedor', 'created_at', 'updated_at'], 'safe'],
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
        $query = Ordendecompratemporal::find();

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
            'idCO' => $this->idCO,
            'idTipoDocumento' => $this->idTipoDocumento,
            'fechaDocumento' => $this->fechaDocumento,
            'idProveedor' => $this->idProveedor,
            'idComprador' => $this->idComprador,
            'idCondicionPago' => $this->idCondicionPago,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'sucursalProveedor', $this->sucursalProveedor]);

        return $dataProvider;
    }
}
