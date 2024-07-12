<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Factura;

/**
 * FacturaSearch represents the model behind the search form of `frontend\modules\ventas\models\Factura`.
 */
class FacturaSearch extends Factura
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'consecutivoDocumento', 'consecutivoDocumentoProveedor', 'created_by', 
            'updated_by', 'idProveedor', ], 'integer'],
            [['centroOperacion', 'tipoDocumento', 'fechaDocumento', 'codigoSucursal', 
            'prefijoDocumentoProveedor', 'fechaDocumentoProveedor', 
            'condicionPago', 'tipoProveedor', 'fechaVencimientoCuota', 'fechaProntoPago', 
            'fechaDesde', 'fechaHasta', 'created_at', 'updated_at'], 'safe'],
            [['valorDocumento', 'porcentajeCuota'], 'number'],
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
        $query = Factura::find();

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
            'consecutivoDocumento' => $this->consecutivoDocumento,
            'fechaDocumento' => $this->fechaDocumento,
            'idProveedor' => $this->idProveedor,
            'consecutivoDocumentoProveedor' => $this->consecutivoDocumentoProveedor,
            'fechaDocumentoProveedor' => $this->fechaDocumentoProveedor,
            'valorDocumento' => $this->valorDocumento,
            'porcentajeCuota' => $this->porcentajeCuota,
            'fechaVencimientoCuota' => $this->fechaVencimientoCuota,
            'fechaProntoPago' => $this->fechaProntoPago,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'centroOperacion', $this->centroOperacion])
            ->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'codigoSucursal', $this->codigoSucursal])
            ->andFilterWhere(['like', 'prefijoDocumentoProveedor', $this->prefijoDocumentoProveedor])
            ->andFilterWhere(['like', 'condicionPago', $this->condicionPago])
            ->andFilterWhere(['like', 'tipoProveedor', $this->tipoProveedor]);

        return $dataProvider;
    }
}
