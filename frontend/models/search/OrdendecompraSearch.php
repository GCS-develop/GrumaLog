<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ordendecompra;

/**
 * OrdendecompraSearch represents the model behind the search form of `frontend\models\Ordendecompra`.
 */
class OrdendecompraSearch extends Ordendecompra
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idCO', 'idTipoDocumento', 'idProveedor', 'idEstado', 'nroPaquetes', 'idTipoDocumentoEntrada', 'idCODocumentoEntrada', 'consecutivoDocumentoEntrada', 'consignacion', 'created_by', 'updated_by'], 'integer'],
            [['consecutivo', 'totalCantidadPedida', 'totalCantidadEntrada', 'totalCantidadPendiente', 'nitcomprador'], 'number'],
            [['fecha', 'fechaEntrega', 'comprador', 'sucursalProveedor', 'fechaDocumentoEntrada', 'created_at', 'updated_at'], 'safe'],
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
        $query = Ordendecompra::find();

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
            'consecutivo' => $this->consecutivo,
            'fecha' => $this->fecha,
            'idProveedor' => $this->idProveedor,
            'idEstado' => $this->idEstado,
            'fechaEntrega' => $this->fechaEntrega,
            'totalCantidadPedida' => $this->totalCantidadPedida,
            'totalCantidadEntrada' => $this->totalCantidadEntrada,
            'totalCantidadPendiente' => $this->totalCantidadPendiente,
            'nroPaquetes' => $this->nroPaquetes,
            'nitcomprador' => $this->nitcomprador,
            'idTipoDocumentoEntrada' => $this->idTipoDocumentoEntrada,
            'idCODocumentoEntrada' => $this->idCODocumentoEntrada,
            'fechaDocumentoEntrada' => $this->fechaDocumentoEntrada,
            'consecutivoDocumentoEntrada' => $this->consecutivoDocumentoEntrada,
            'consignacion' => $this->consignacion,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'comprador', $this->comprador])
            ->andFilterWhere(['like', 'sucursalProveedor', $this->sucursalProveedor]);

        $query->orderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }
}
