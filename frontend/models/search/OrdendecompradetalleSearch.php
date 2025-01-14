<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Ordendecompradetalle;

/**
 * OrdendecompradetalleSearch represents the model behind the search form of `frontend\models\Ordendecompradetalle`.
 */
class OrdendecompradetalleSearch extends Ordendecompradetalle
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idOrdenCompra', 'idItem', 'idCategoria', 'idSubcategoria', 'created_by', 'updated_by'], 'integer'],
            [['cantidadPedida', 'cantidadEntrada', 'cantidadPendiente'], 'number'],
            [['fechaEntrega', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $idordencompra = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  
                                'det.idOrdenCompra', 
                                'det.idItem',
                                'oc.consecutivo',
                                'oc.fecha AS fecha_activacion',
                                'it.item',
                                'it.referencia',
                                'it.descripcion',
                                'it.codigoBarras AS codigoEAN',
                                "ISNULL(it.unidadEmpaque,'UND') AS unidadEmpaque",
                                'mar.nombre AS marca',
                                'tal.nombre AS talla',
                                'col.nombre AS color',
                                'cat.nombre AS categoria', 
                                'sub.nombre AS subcategoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'ordendecompra oc','det.idOrdenCompra = oc.id')
                    ->join('INNER JOIN', 'item it','det.idItem = it.id')
                    ->join('INNER JOIN', 'categoria cat','it.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','it.idSubcategoria = sub.id')
                    ->join('LEFT JOIN', 'marca mar','it.idMarca = mar.id')
                    ->join('LEFT JOIN', 'talla tal','it.idTalla = tal.id')
                    ->join('LEFT JOIN', 'color col','it.idColor = col.id')
                    ->groupBy(['det.idOrdenCompra', 
                                'det.idItem',
                                'oc.consecutivo',
                                'oc.fecha',
                                'it.item',
                                'it.referencia',
                                'it.descripcion',
                                'it.codigoBarras',
                                "ISNULL(it.unidadEmpaque,'UND')",
                                'mar.nombre',
                                'tal.nombre',
                                'col.nombre',
                                'cat.nombre', 
                                'sub.nombre'
        ]   );

        if ($idordencompra){
            $query = $query->andFilterWhere(['det.idOrdenCompra' => $idordencompra]);
        }

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
            'idItem' => $this->idItem,
            'idCategoria' => $this->idCategoria,
            'idSubcategoria' => $this->idSubcategoria,
            'cantidadPedida' => $this->cantidadPedida,
            'cantidadEntrada' => $this->cantidadEntrada,
            'cantidadPendiente' => $this->cantidadPendiente,
            'fechaEntrega' => $this->fechaEntrega,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }

    public function searchDetalleItem($idordencompra = null, $idcategoria = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'det.idItem',
                                'oc.consecutivo',
                                'it.item',
                                'it.referencia',
                                "ISNULL(it.unidadEmpaque,'UND') AS unidadEmpaque",
                                'mar.nombre AS marca',
                                'tal.nombre AS talla',
                                'col.nombre AS color',
                                'cat.nombre AS categoria', 
                                'sub.nombre AS subcategoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'ordendecompra oc','det.idOrdenCompra = oc.id')
                    ->join('INNER JOIN', 'item it','det.idItem = it.id')
                    ->join('INNER JOIN', 'categoria cat','it.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','it.idSubcategoria = sub.id')
                    ->join('LEFT JOIN', 'marca mar','it.idMarca = mar.id')
                    ->join('LEFT JOIN', 'talla tal','it.idTalla = tal.id')
                    ->join('LEFT JOIN', 'color col','it.idColor = col.id')
                    ->groupBy(['det.idOrdenCompra', 
                                'det.idItem',
                                'oc.consecutivo',
                                'it.item',
                                'it.referencia',
                                "ISNULL(it.unidadEmpaque,'UND')",
                                'mar.nombre',
                                'tal.nombre',
                                'col.nombre',
                                'cat.nombre', 
                                'sub.nombre'
                            ])
                    ->andFilterWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['det.idCategoria' => $idcategoria]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function searchDetalleItem1($idordencompra = null, $idcategoria = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'it.item',
                                'it.referencia', 
                                'it.descripcion',
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'item it','det.idItem = it.id')
                    ->groupBy(['det.idOrdenCompra', 
                                'it.item',
                                'it.referencia',
                                'it.descripcion'
                            ])
                    ->andFilterWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['det.idCategoria' => $idcategoria]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }


    public function searchResumenSubcategoria($idordencompra, $idcategoria = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'cat.nombre AS categoria', 
                                'sub.nombre AS subcategoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'categoria cat','det.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','det.idSubcategoria = sub.id')
                    ->groupBy(['det.idOrdenCompra', 'cat.nombre' , 'sub.nombre'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['det.idCategoria' => $idcategoria]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function searchResumenCategoria($idordencompra, $idCategoria = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'cat.nombre AS categoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'categoria cat','det.idCategoria = cat.id')
                    ->groupBy(['det.idOrdenCompra', 'cat.nombre'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['det.idCategoria' => $idCategoria]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

}
