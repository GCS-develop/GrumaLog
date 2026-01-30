<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Pedidoordendecompraitem;

/**
 * PedidoordendecompraitemSearch represents the model behind the search form of `frontend\models\Pedidoordendecompraitem`.
 */
class PedidoordendecompraitemSearch extends Pedidoordendecompraitem
{
    public $bodega_codigo;
    public $bodega_nombre;
    public $item_codigobarras;
    public $color_codigo;
    public $talla_codigo;
    public $item_numero;
    public $consecutivo;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'id',
                'idPedido',
                'idOrdenCompra',
                'idItem',
                'idBodega',
                'totalUnidades',
                'created_by',
                'updated_by'
            ], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [[
                'bodega_codigo',
                'bodega_nombre',
                'item_codigobarras',
                'item_numero',
                'color_codigo',
                'talla_codigo',
                'bodega_cod_origen',
                'bodega_nmb_origen',
                'consecutivo'
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
    public function search($params, $idpedido = null, $idordencompra = null)
    {
        $query = Pedidoordendecompraitem::find()
            ->alias('pd')
            ->joinWith([
                'pedido p',                     // alias "p"
                'ordencompra oc',               // alias "o"
                'bodega b',                     // alias "b"
                'ordencompradetalle det',
                'item i' => function ($q) {
                    $q->joinWith([
                        'color c',              // alias "c"
                        'talla t',              // alias "t"
                        'categoria cat',
                        'subcategoria sub',
                    ]);
                },
            ])
            ->andFilterWhere(['pd.idPedido' => $idpedido])
            ->andFilterWhere(['pd.idOrdenCompra' => $idordencompra]);

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
            'idPedido' => $this->idPedido,
            'idOrdenCompra' => $this->idOrdenCompra,
            'idItem' => $this->idItem,
            'idBodega' => $this->idBodega,
            'totalUnidades' => $this->totalUnidades,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        // filtros de los atributos virtuales
        $query->andFilterWhere(['like', 'b.codigo', $this->bodega_codigo])
            ->andFilterWhere(['like', 'b.nombre', $this->bodega_nombre])
            ->andFilterWhere(['like', 'i.codigoBarras', $this->item_codigobarras])
            ->andFilterWhere(['like', 'c.codigo', $this->color_codigo])
            ->andFilterWhere(['like', 't.codigo', $this->talla_codigo]);

        $query->orderBy([
            'pd.idPedido' => SORT_DESC,
            'pd.idOrdenCompra' => SORT_ASC
        ]);

        return $dataProvider;
    }
}
