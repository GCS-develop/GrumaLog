<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ordendecompratemporalitem;

/**
 * OrdendecompratemporalitemSearch represents the model behind the search form of `frontend\models\Ordendecompratemporalitem`.
 */
class OrdendecompratemporalitemSearch extends Ordendecompratemporalitem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idOrdenCompra', 'numeroRegistro', 'idBodega', 'idCOMovimiento', 'cantidadPedida', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['codigoMotivo', 'fechaEntrega', 'created_at', 'updated_at'], 'safe'],
            [['precioUnitario'], 'number'],
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
    public function search($params, $idordencompra)
    {
        $query = Ordendecompratemporalitem::find()->where(['idOrdenCompra' => $idordencompra]);

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
            'idOrdenCompra' => $this->idOrdenCompra,
            'numeroRegistro' => $this->numeroRegistro,
            'idBodega' => $this->idBodega,
            'idCOMovimiento' => $this->idCOMovimiento,
            'cantidadPedida' => $this->cantidadPedida,
            'fechaEntrega' => $this->fechaEntrega,
            'precioUnitario' => $this->precioUnitario,
            'idItem' => $this->idItem,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoMotivo', $this->codigoMotivo]);

        return $dataProvider;
    }
}
