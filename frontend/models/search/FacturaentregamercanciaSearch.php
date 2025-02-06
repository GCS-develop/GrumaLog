<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Facturaentregamercancia;

/**
 * FacturaentregamercanciaSearch represents the model behind the search form of `frontend\models\Facturaentregamercancia`.
 */
class FacturaentregamercanciaSearch extends Facturaentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaEntregaMercancia', 'idProgramacionEntregaMercancia', 'created_by', 'updated_by'], 'integer'],
            [['numeroFactura', 'observaciones', 'created_at', 'updated_at', 'consecutivo',
            'nit', 'razonSocial', 'almacen', 'serie', 'radicado', 'numeroFacturaLegaliza'], 'safe'],
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
        $query = Facturaentregamercancia::find()->alias('fem');
        $query->join('INNER JOIN', 'agendaentregamercancia aem', 'fem.idAgendaEntregaMercancia = aem.id');
        $query->join('INNER JOIN', 'ordendecompra oc', 'aem.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'categoria cat', 'aem.idCategoria = cat.id');

        $query->select([
            'fem.id',
            'aem.id AS radicado', 
            'co.codigo AS almacen', 
            'td.codigo AS serie', 
            'oc.consecutivo', 
            'aem.idCategoria', 
            'cat.nombre AS categoria',
            'oc.idProveedor', 
            'pr.nit', 
            'pr.razonSocial', 
            'fem.numeroFactura',
            'fem.observaciones',
            'fem.numeroFacturaLegaliza',
            'fem.consecutivoDocumentoEntrada',
            'fem.idTipoDocumentoEntrada',
        ]);

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
            'fem.id' => $this->id,
            'fem.idAgendaEntregaMercancia' => $this->idAgendaEntregaMercancia,
            'fem.idProgramacionEntregaMercancia' => $this->idProgramacionEntregaMercancia,
            'fem.created_at' => $this->created_at,
            'fem.created_by' => $this->created_by,
            'fem.updated_at' => $this->updated_at,
            'fem.updated_by' => $this->updated_by,
            'oc.consecutivo' => $this->consecutivo,
            'aem.id' => $this->radicado,
            'td.codigo' => $this->serie,
            'pr.nit' => $this->nit
        ]);

        $query->andFilterWhere(['like', 'fem.numeroFactura', $this->numeroFactura])
            ->andFilterWhere(['like', 'fem.numeroFacturaLegaliza', $this->numeroFacturaLegaliza])
            ->andFilterWhere(['like', 'pr.razonSocial', $this->razonSocial])
            ->andFilterWhere(['like', 'fem.observaciones', $this->observaciones]);

        $query->orderBy([   
                                    //'aem.created_at' => SORT_DESC
                                    'fem.created_at' => SORT_DESC,
                                    'pr.nit' => SORT_ASC
                                ]
                        );

        return $dataProvider;
    }
}
