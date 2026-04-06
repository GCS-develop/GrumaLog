<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferenciaordencompraexcel;

/**
 * TransferenciaordencompraexcelSearch represents the model behind the search form of `frontend\models\Transferenciaordencompraexcel`.
 */
class TransferenciaordencompraexcelSearch extends Transferenciaordencompraexcel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTransferenciaerp', 'consecutivoDocumento', 'consignacion', 'consecutivoOrdenCompra', 'consecutivoMovimiento', 'numeroRegistroMovimiento', 'cantidadBase', 'item', 'rowid'], 'integer'],
            [['centroOperacionDocumento', 'tipoDocumento', 'fechaDocumento', 'tercero', 'numeroFactura', 'sucursal', 'idTerceroComprador', 'centroOperacionOrdenCompra', 'tipoDocumentoOrdenCompra', 'centroOperacionMovimiento', 'tipoDocumentoMovimiento', 'bodegaMovimiento', 'unidadMovimiento', 'fechaEntregaMovimiento', 'color', 'talla'], 'safe'],
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
    public function search($params, $idtransferenciaerp)
    {
        $groupFields = [
            'idTransferenciaerp',
            'centroOperacionDocumento',
            'tipoDocumento',
            'consecutivoDocumento',
            'fechaDocumento',
            'tercero',
            'numeroFactura',
            'sucursal',
            'idTerceroComprador',
            'consignacion',
            'centroOperacionOrdenCompra',
            'tipoDocumentoOrdenCompra',
            'consecutivoOrdenCompra',
            'centroOperacionMovimiento',
            'tipoDocumentoMovimiento',
            'consecutivoMovimiento',
            'numeroRegistroMovimiento',
            'bodegaMovimiento',
            'unidadMovimiento',
            'fechaEntregaMovimiento',
            'item',
            'color',
            'talla',
            'rowid',
            'codigoUnidadEmpaque',
        ];

        $query = Transferenciaordencompraexcel::find()
            ->select(array_merge($groupFields, [
                'MIN(id) as id',
                'SUM(cantidadBase) as cantidadBase',
                'SUM(unidadesConteoEmpaque) as unidadesConteoEmpaque',
            ]))
            ->where(['idTransferenciaerp' => $idtransferenciaerp])
            ->groupBy($groupFields);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false
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
            'idTransferenciaerp' => $this->idTransferenciaerp,
            'consecutivoDocumento' => $this->consecutivoDocumento,
            'consignacion' => $this->consignacion,
            'consecutivoOrdenCompra' => $this->consecutivoOrdenCompra,
            'consecutivoMovimiento' => $this->consecutivoMovimiento,
            'numeroRegistroMovimiento' => $this->numeroRegistroMovimiento,
            'cantidadBase' => $this->cantidadBase,
            'item' => $this->item,
            'rowid' => $this->rowid,
        ]);

        $query->andFilterWhere(['like', 'centroOperacionDocumento', $this->centroOperacionDocumento])
            ->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'fechaDocumento', $this->fechaDocumento])
            ->andFilterWhere(['like', 'tercero', $this->tercero])
            ->andFilterWhere(['like', 'numeroFactura', $this->numeroFactura])
            ->andFilterWhere(['like', 'sucursal', $this->sucursal])
            ->andFilterWhere(['like', 'idTerceroComprador', $this->idTerceroComprador])
            ->andFilterWhere(['like', 'centroOperacionOrdenCompra', $this->centroOperacionOrdenCompra])
            ->andFilterWhere(['like', 'tipoDocumentoOrdenCompra', $this->tipoDocumentoOrdenCompra])
            ->andFilterWhere(['like', 'centroOperacionMovimiento', $this->centroOperacionMovimiento])
            ->andFilterWhere(['like', 'tipoDocumentoMovimiento', $this->tipoDocumentoMovimiento])
            ->andFilterWhere(['like', 'bodegaMovimiento', $this->bodegaMovimiento])
            ->andFilterWhere(['like', 'unidadMovimiento', $this->unidadMovimiento])
            ->andFilterWhere(['like', 'fechaEntregaMovimiento', $this->fechaEntregaMovimiento])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla]);

        return $dataProvider;
    }
}
