<?php

namespace frontend\models\Search;

use frontend\models\Pedido;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Pedidodetalle;

/**
 * PedidodetalleSearch represents the model behind the search form of `frontend\models\Pedidodetalle`.
 */
class PedidodetalleSearch extends Pedidodetalle
{
    public $bodega_codigo;
    public $bodega_nombre;
    public $item_codigobarras;
    public $color_codigo;
    public $talla_codigo;
    public $item_numero;
    public $bodega_cod_origen;
    public $bodega_nmb_origen;
    public $oc_consecutivo;
    // En PedidodetalleSearch
    public $total_unidades;
    public $total_recibidas;
    public $completo;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'id',
                'idPedido',
                'idBodega',
                'idItem',
                'unidades',
                'created_by',
                'updated_by',
                'completo',
                'item_item',
            ], 'integer'],
            [['total_unidades', 'total_recibidas'], 'number'],
            // atributos virtuales como "safe"
            [[
                'bodega_codigo',
                'bodega_nombre',
                'item_codigobarras',
                'item_numero',
                'color_codigo',
                'talla_codigo',
                'bodega_cod_origen',
                'bodega_nmb_origen',
                'oc_consecutivo'
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
    public function search($params, $forExport = false, $idpedido = null, $idordencompra = null)
    {
        // 🔹 Query base
        $query = Pedidodetalle::find()->alias('pd');

        // 🔹 Joins básicos (solo los necesarios)
        $query->joinWith([
            'pedido p',
            'ordencompra oc',
            'bodega b',
        ]);

        // 🔹 Si NO es exportación, agregamos los joins pesados
        if (!$forExport) {
            $query->joinWith([
                'pedidoordencompraitem poci' => function ($q) {
                    $q->joinWith(['bodega bo']);
                },
                'item i' => function ($q) {
                    $q->joinWith([
                        'color c',
                        'talla t',
                        'categoria cat',
                        'subcategoria sub',
                    ]);
                },
            ]);
        } else {
            // 🔹 En modo export solo lo esencial (evita joins que multiplican registros)
            $query->joinWith([
                'item i' => function ($q) {
                    $q->joinWith([
                        'color c',
                        'talla t',
                    ]);
                },
            ]);
        }

        // 🔹 Filtros iniciales
        $query->andFilterWhere([
            'pd.idPedido' => $idpedido,
            'pd.idOrdenCompra' => $idordencompra,
        ]);

        // 🔹 Definición del dataProvider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => $forExport ? false : ['pageSize' => 120],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            /*'pd.id' => $this->id,
            'pd.idPedido' => $this->idPedido,
            'pd.idBodega' => $this->idBodega,
            'pd.idItem' => $this->idItem,
            'pd.unidades' => $this->unidades,
            'pd.created_at' => $this->created_at,
            'pd.created_by' => $this->created_by,
            'pd.updated_at' => $this->updated_at,
            'pd.updated_by' => $this->updated_by,*/
            'pd.idPedido' => $this->idPedido,
            'i.item' => $this->item_numero,
            'oc.consecutivo' => $this->oc_consecutivo
        ]);

        $query->andFilterWhere(['like', 'b.codigo', $this->bodega_codigo])
            ->andFilterWhere(['like', 'b.nombre', $this->bodega_nombre])
            ->andFilterWhere(['like', 'i.codigoBarras', $this->item_codigobarras])
            ->andFilterWhere(['like', 'c.codigo', $this->color_codigo])
            ->andFilterWhere(['like', 't.codigo', $this->talla_codigo]);

        $query->orderBy([
            'pd.idPedido' => SORT_DESC,
            'pd.idOrdenCompra' => SORT_ASC,
            'i.item' => SORT_ASC,
            'c.codigo' => SORT_ASC,
            't.codigo' => SORT_ASC,
            'b.codigo' => SORT_ASC

        ]);

        return $dataProvider;
    }


    /**
     * Consolidado de pedidos por tienda 
     */
    public function searchConsolidado($params, $forExport = false, $idpedido = null, $idordencompra = null)
    {
        $query = Pedidodetalle::find()->alias('pd');
        $query->join('INNER JOIN', 'Bodegas b', 'b.id = pd.idBodega');
        $query->join('INNER JOIN', 'item i', 'i.id = pd.idItem');
        $query->join('INNER JOIN', 'ordendecompra oc', 'oc.id = pd.idOrdenCompra');

        // SELECT: agrega b.codigo y b.nombre para poder ordenar por ellos en SQL Server
        $query->select([
            'pd.idOrdenCompra',                 // ⬅️ nuevo
            'oc.consecutivo AS oc_consecutivo',
            'pd.idPedido',
            'pd.idBodega',
            'b.codigo AS bodega_codigo',
            'b.nombre AS bodega_nombre',
            'i.item AS item_item',
            'total_unidades'  => new \yii\db\Expression('SUM(pd.unidades)'),
            'total_recibidas' => new \yii\db\Expression('SUM(COALESCE(pd.unidadesRecibidas,0))'),
            'completo'        => new \yii\db\Expression('CASE WHEN SUM(COALESCE(pd.unidadesRecibidas,0)) >= SUM(pd.unidades) THEN 1 ELSE 0 END'),
        ]);

        // Filtros base por argumentos (opcionales)
        $query->andFilterWhere([
            'pd.idPedido'      => $idpedido,
            'pd.idOrdenCompra' => $idordencompra,
        ]);

        // GROUP BY debe incluir toda columna no agregada que uses (SQL Server)
        $query->groupBy([
            'pd.idPedido',
            'pd.idBodega',
            'b.id',
            'i.item',
            'b.codigo',
            'b.nombre',
            'pd.idOrdenCompra',
            'oc.consecutivo',
        ]);

        // Ahora sí puedes ordenar por b.codigo sin error
        $query->orderBy([
            'pd.idPedido' => SORT_DESC,
            'b.codigo'    => SORT_ASC,
            'oc.consecutivo' => SORT_ASC,
            'i.item'         => SORT_ASC,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => $forExport ? false : ['pageSize' => 120],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros del formulario
        $query->andFilterWhere(['pd.idPedido'      => $this->idPedido]);
        $query->andFilterWhere(['pd.idOrdenCompra' => $this->idOrdenCompra]);

        $query->andFilterWhere(['like', 'b.codigo', $this->bodega_codigo])
            ->andFilterWhere(['like', 'b.nombre', $this->bodega_nombre]);

        if (!empty($this->item_item)) {
            $query->andFilterWhere(['like', 'i.item', $this->item_item]);
        }
        if (!empty($this->oc_consecutivo)) {
            $query->andFilterWhere(['oc.consecutivo' => $this->oc_consecutivo]);
        }

        if ($this->completo !== null && $this->completo !== '') {
            $query->having(new \yii\db\Expression(
                '(CASE WHEN SUM(COALESCE(pd.unidadesRecibidas,0)) >= SUM(pd.unidades) THEN 1 ELSE 0 END) = :comp',
                [':comp' => (int)$this->completo]
            ));
        }

        return $dataProvider;
    }

    // En frontend/models/Search/PedidodetalleSearch.php

    public function searchPorTienda($params, $forExport = false, $idpedido = null)
    {
        $query = Pedidodetalle::find()->alias('pd');

        // Solo lo esencial para el macro
        $query->join('INNER JOIN', 'Bodegas b', 'b.id = pd.idBodega');
        $query->join('INNER JOIN', 'Pedido p', 'p.id = pd.idPedido');

        // SELECT con agregados
        $query->select([
            'pd.idPedido',
            'pd.idBodega',
            'b.codigo AS bodega_codigo',
            'b.nombre AS bodega_nombre',
            'total_unidades'  => new \yii\db\Expression('SUM(pd.unidades)'),
            'total_recibidas' => new \yii\db\Expression('SUM(COALESCE(pd.unidadesRecibidas,0))'),
        ]);

        // Filtros base por argumento
        $query->andFilterWhere(['pd.idPedido' => $idpedido]);

        // GROUP BY (toda columna no agregada)
        $query->groupBy([
            'pd.idPedido',
            'pd.idBodega',
            'b.codigo',
            'b.nombre',
        ]);

        // Orden lógico: último pedido primero, luego tienda
        $query->orderBy([
            'pd.idPedido' => SORT_DESC,
            'b.codigo'    => SORT_ASC,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => $forExport ? false : ['pageSize' => 120],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros del formulario (opcionales)
        $query->andFilterWhere(['pd.idPedido' => $this->idPedido]);

        if (is_array($this->bodega_codigo) && !empty($this->bodega_codigo)) {
            // Select2 devuelve IDs -> filtra por b.id
            $query->andWhere(['IN', 'b.id', $this->bodega_codigo]);
        } elseif (!empty($this->bodega_codigo)) {
            // Si llega un único id en texto, igual por b.id
            $query->andWhere(['b.id' => $this->bodega_codigo]);
        }

        $query->andFilterWhere(['like', 'b.nombre', $this->bodega_nombre]);

        return $dataProvider;
    }
}
