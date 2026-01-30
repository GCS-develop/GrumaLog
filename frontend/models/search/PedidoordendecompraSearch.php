<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Pedidoordendecompra;

/**
 * PedidoordendecompraSearch represents the model behind the search form of `frontend\models\Pedidoordendecompra`.
 */
class PedidoordendecompraSearch extends Pedidoordendecompra
{
    public $consecutivo;     // ajusta al nombre real: consecutivo / numero / etc.
    public $proveedor_razonSocial;
    public $tipoDocumento;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idPedido', 'idOrdenCompra', 'nroItems', 'totalUnidades', 'created_by', 'updated_by'], 'integer'],
            [[
                'nombreArchivo',
                'created_at',
                'updated_at',
                'consecutivo',
                'tipoDocumento',
                'proveedor_razonSocial'
            ], 'safe'],
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
    public function search($params, $idpedido)
    {
        $query = Pedidoordendecompra::find()->alias('poc')
            ->joinWith(
                [
                    'ordencompra oc',
                    'ordencompra.proveedor pr',
                    'ordencompra.tipoDocumento td'
                ]
            ) // LEFT JOIN por defecto
            ->where(['poc.idPedido' => $idpedido]);


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
            'poc.id' => $this->id,
            'poc.idPedido' => $this->idPedido,
            'poc.idOrdenCompra' => $this->idOrdenCompra,
            'poc.nroItems' => $this->nroItems,
            'poc.totalUnidades' => $this->totalUnidades,
            'poc.created_at' => $this->created_at,
            'poc.created_by' => $this->created_by,
            'poc.updated_at' => $this->updated_at,
            'poc.updated_by' => $this->updated_by,

            'oc.consecutivo' => $this->consecutivo,
            'td.codigo' => $this->tipoDocumento,
        ]);

        $query->andFilterWhere(['like', 'poc.nombreArchivo', $this->nombreArchivo])
            ->andFilterWhere(['like', 'pr.razonSocial', $this->proveedor_razonSocial]);

        return $dataProvider;
    }
}
