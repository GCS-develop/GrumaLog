<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\ventasimportadas;
use Yii;

/**
 * VentasimportadasSearch represents the model behind the search form of `frontend\models\ventasimportadas`.
 */
class VentasimportadasSearch extends ventasimportadas
{
    public $fecha_inicio;
    public $fecha_fin;
    public $proveedor;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'rowid_item_ext', 'unidades', 'factor'], 'integer'],
            [['codigoCentroOperacion', 'nombreCentroOperacion', 'fecha', 'item', 'codigobarra', 'descripcion', 'descripcionCorta', 'color', 'talla', 'referencia', 'nombreproveedor', 'proveedor', 'unidadmedida', 'created_at'], 'safe'],
            [['precio_aplicado', 'total', 'costo_prom_tot', 'costo_prom_mp'], 'number'],
            [['fecha_inicio', 'fecha_fin'], 'safe'],
            [['proveedor'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ventasimportadas::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros adicionales por fecha y proveedor
        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->andFilterWhere(['between', 'fecha', $this->fecha_inicio, $this->fecha_fin]);
        }

        if ($this->proveedor) {
            $query->andFilterWhere(['proveedor' => $this->proveedor]);
        }

        // Filtros generales
        $query->andFilterWhere([
            'id' => $this->id,
            'fecha' => $this->fecha,
            'rowid_item_ext' => $this->rowid_item_ext,
            'unidades' => $this->unidades,
            'precio_aplicado' => $this->precio_aplicado,
            'total' => $this->total,
            'costo_prom_tot' => $this->costo_prom_tot,
            'costo_prom_mp' => $this->costo_prom_mp,
            'factor' => $this->factor,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'codigoCentroOperacion', $this->codigoCentroOperacion])
            ->andFilterWhere(['like', 'nombreCentroOperacion', $this->nombreCentroOperacion])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'codigobarra', $this->codigobarra])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'descripcionCorta', $this->descripcionCorta])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'nombreproveedor', $this->nombreproveedor])
            ->andFilterWhere(['like', 'proveedor', $this->proveedor])
            ->andFilterWhere(['like', 'unidadmedida', $this->unidadmedida]);

        return $dataProvider;
    }

    public function importarDesdeSiesa($inicio, $fin, $proveedor)
{
    $sql = <<<SQL
    SELECT
        bod.f150_id AS codigoCentroOperacion,
        bod.f150_descripcion AS nombreCentroOperacion,
        CAST(vta.f9930_id_fecha_factura AS DATE) AS fecha,
        vta.f9930_rowid_item_ext,
        it.f120_id AS item,
        itx.f121_id_barras_principal AS codigobarra,
        TRIM(it.f120_descripcion) AS descripcion,
        TRIM(it.f120_descripcion_corta) AS descripcionCorta,
        TRIM(itx.f121_id_ext1_detalle) AS color,
        TRIM(itx.f121_id_ext2_detalle) AS talla,
        TRIM(it.f120_referencia) AS referencia,
        TRIM(itcm4.f106_descripcion) AS nombreproveedor,
        TRIM(itcm4.f106_id) AS proveedor,

        CASE WHEN vta.f9930_ind_naturaleza = 1
             THEN vta.f9930_cant_base * -1 * vta.f9930_factor
             ELSE vta.f9930_cant_base *  vta.f9930_factor
        END AS unidades,

        -- precio_aplicado usando t126 (vigente por fecha y UM)
        ROUND(
          ISNULL(
            ISNULL(precio_vigente.f126_precio, precio_minimo.f126_precio),
            vta.f9930_costo_prom_tot
          ), 0
        ) AS precio_aplicado,

        CASE WHEN vta.f9930_ind_naturaleza = 1
             THEN vta.f9930_cant_base * ROUND(ISNULL(ISNULL(precio_vigente.f126_precio, precio_minimo.f126_precio), vta.f9930_costo_prom_tot),0) * -1 * vta.f9930_factor
             ELSE vta.f9930_cant_base * ROUND(ISNULL(ISNULL(precio_vigente.f126_precio, precio_minimo.f126_precio), vta.f9930_costo_prom_tot),0)      * vta.f9930_factor
        END AS total,

        vta.f9930_costo_prom_tot,
        vta.f9930_costo_prom_mp,
        vta.f9930_factor AS factor,
        vta.f9930_id_unidad_medida AS unidadmedida

    FROM t9930_pdv_a_movto_venta vta
    INNER JOIN t121_mc_items_extensiones itx ON vta.f9930_rowid_item_ext = itx.f121_rowid
    INNER JOIN t120_mc_items it           ON itx.f121_rowid_item = it.f120_rowid
    INNER JOIN t150_mc_bodegas bod        ON bod.f150_rowid      = vta.f9930_rowid_bodega

    LEFT JOIN t125_mc_items_criterios itc_prov
        ON it.f120_rowid = itc_prov.f125_rowid_item AND itc_prov.f125_id_plan = '015'
    LEFT JOIN t106_mc_criterios_item_mayores itcm4
        ON itc_prov.f125_id_plan = itcm4.f106_id_plan AND itc_prov.f125_id_criterio_mayor = itcm4.f106_id

    LEFT JOIN t125_mc_items_criterios itc_tipinv
        ON it.f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
    LEFT JOIN t106_mc_criterios_item_mayores itcm8
        ON itc_tipinv.f125_id_plan = itcm8.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm8.f106_id

    -- Precio vigente en t126 por fecha de activación/inactivación, UM y lista
    OUTER APPLY (
        SELECT TOP 1 t126.f126_precio
        FROM t126_mc_items_precios t126
        WHERE t126.f126_rowid_item = it.f120_rowid
          AND t126.f126_id_unidad_medida IN ('UND')
          -- si usas lista específica, descomenta/ajusta:
          -- AND t126.f126_id_lista_precio IN ('04')
          AND CAST(t126.f126_fecha_activacion AS DATE) <= CAST(vta.f9930_id_fecha_factura AS DATE)
          AND (
               t126.f126_fecha_inactivacion IS NULL
               OR CAST(t126.f126_fecha_inactivacion AS DATE) >= CAST(vta.f9930_id_fecha_factura AS DATE)
          )
        ORDER BY t126.f126_fecha_activacion DESC
    ) AS precio_vigente

    -- Precio mínimo histórico (primera activación conocida)
    OUTER APPLY (
        SELECT TOP 1 t126min.f126_precio
        FROM t126_mc_items_precios t126min
        WHERE t126min.f126_rowid_item = it.f120_rowid
          AND t126min.f126_id_unidad_medida IN ('UND')
          -- AND t126min.f126_id_lista_precio IN ('02') -- si aplica una lista distinta para “mínimo”
        ORDER BY t126min.f126_fecha_activacion ASC
    ) AS precio_minimo

    WHERE TRIM(itcm4.f106_id) = :proveedor
      AND itcm8.f106_id_plan = '008' AND itcm8.f106_id = '0001'
      AND CAST(vta.f9930_id_fecha_factura AS DATE) BETWEEN :inicio AND :fin
SQL;

    try {
        $ventas = Yii::$app->dbSiesa->createCommand($sql, [
            ':inicio'    => $inicio,
            ':fin'       => $fin,
            ':proveedor' => trim($proveedor),
        ])->queryAll();
    } catch (\Throwable $e) {
        Yii::$app->session->setFlash('error', 'Error Siesa: '.$e->getMessage());
        return [];
    }

    Yii::$app->db->createCommand()->truncateTable('ventasimportadas')->execute();

    foreach ($ventas as $venta) {
        Yii::$app->db->createCommand()->insert('ventasimportadas', [
            'codigoCentroOperacion' => $venta['codigoCentroOperacion'],
            'nombreCentroOperacion' => $venta['nombreCentroOperacion'],
            'fecha'                 => $venta['fecha'],
            'rowid_item_ext'        => $venta['f9930_rowid_item_ext'],
            'item'                  => $venta['item'],
            'codigobarra'           => $venta['codigobarra'],
            'descripcion'           => $venta['descripcion'],
            'descripcionCorta'      => $venta['descripcionCorta'],
            'color'                 => $venta['color'],
            'talla'                 => $venta['talla'],
            'referencia'            => $venta['referencia'],
            'nombreproveedor'       => $venta['nombreproveedor'],
            'proveedor'             => $venta['proveedor'],
            'unidades'              => $venta['unidades'],
            'precio_aplicado'       => $venta['precio_aplicado'],
            'total'                 => $venta['total'],
            'costo_prom_tot'        => $venta['f9930_costo_prom_tot'],
            'costo_prom_mp'         => $venta['f9930_costo_prom_mp'],
            'factor'                => $venta['factor'],
            'unidadmedida'          => $venta['unidadmedida'],
        ])->execute();
    }

    return $ventas;
}


// frontend/models/search/VentasimportadasSearch.php
public function searchGrouped($params)
{
    $this->load($params);

    $f1   = $this->fecha_inicio;
    $f2   = $this->fecha_fin;
    $prov = $this->proveedor;

    if (!$f1 || !$f2 || !$prov) {
        return new \yii\data\ArrayDataProvider([
            'allModels'  => [],
            'pagination' => false,
        ]);
    }

    // Agrupamos por codigobarra y bodega (sin mostrar codigobarra)
    $sql = "
        SELECT
            -- valores representativos (constantes por codigobarra)
            MIN(item)  AS item,
            MIN(color) AS color,
            MIN(talla) AS talla,

            codigoCentroOperacion AS bodega,

            SUM(CAST(unidades AS float)) AS unidades_sum,
            SUM(CAST(total    AS float)) AS total_sum,

            CASE WHEN SUM(CAST(unidades AS float)) <> 0
                 THEN SUM(CAST(total AS float)) / SUM(CAST(unidades AS float))
                 ELSE 0 END AS precio_promedio
        FROM ventasimportadas
        WHERE fecha BETWEEN :f1 AND :f2
          AND proveedor = :prov
        GROUP BY codigobarra, codigoCentroOperacion
        ORDER BY MIN(item), MIN(color), MIN(talla), codigoCentroOperacion
    ";

    $rows = \Yii::$app->db->createCommand($sql, [
        ':f1'   => $f1,
        ':f2'   => $f2,
        ':prov' => $prov,
    ])->queryAll();

    return new \yii\data\ArrayDataProvider([
        'allModels'  => $rows,
        'pagination' => false, // todo en una sola página
        'sort'       => [
            'attributes' => [
                'item','color','talla','bodega','unidades_sum','precio_promedio','total_sum'
            ],
            'defaultOrder' => ['item' => SORT_ASC, 'bodega' => SORT_ASC],
        ],
    ]);
}




}
