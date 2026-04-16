<?php

namespace frontend\modules\compras\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class PortalVentasSearch extends Model
{
    public $item;
    public $fecha;
    public $descripcion;
    public $nombreCentroOperacion;
    public $codigobarra;
    public $fechaDesde;
    public $fechaHasta;

    public function rules()
    {
        return [
            [['item', 'fecha', 'descripcion', 'codigobarra', 'nombreCentroOperacion'], 'safe'],
        ];
    }

    public function search($params, $codigo, $fechaDesde, $fechaHasta)
    {
        $fechaDesde      = Yii::$app->dbSiesa->quoteValue($fechaDesde);
        $fechaHasta      = Yii::$app->dbSiesa->quoteValue($fechaHasta);
        $codigoProveedor = Yii::$app->dbSiesa->quoteValue($codigo);

        $sql = <<<SQL
        (
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
        CASE
            WHEN vta.f9930_ind_naturaleza = 1 THEN vta.f9930_cant_base * -1 * vta.f9930_factor
            ELSE vta.f9930_cant_base * vta.f9930_factor
        END AS unidades,
        ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio), vta.f9930_costo_prom_tot),0) AS precio_aplicado,
        CASE
            WHEN vta.f9930_ind_naturaleza = 1
            THEN vta.f9930_cant_base * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio), vta.f9930_costo_prom_tot),0) * -1 * vta.f9930_factor
            ELSE vta.f9930_cant_base * ROUND(ISNULL(ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio), vta.f9930_costo_prom_tot),0) * vta.f9930_factor
        END AS total,
        vta.f9930_costo_prom_tot,
        vta.f9930_costo_prom_mp,
        vta.f9930_factor AS factor,
        vta.f9930_id_unidad_medida AS unidadmedida
        FROM t9930_pdv_a_movto_venta vta
        INNER JOIN t121_mc_items_extensiones itx ON vta.f9930_rowid_item_ext = itx.f121_rowid
        INNER JOIN t120_mc_items it ON itx.f121_rowid_item = it.f120_rowid
        INNER JOIN t150_mc_bodegas bod ON bod.f150_rowid = vta.f9930_rowid_bodega
        LEFT JOIN t125_mc_items_criterios itc_prov ON it.f120_rowid = itc_prov.f125_rowid_item AND itc_prov.f125_id_plan = '015'
        LEFT JOIN t106_mc_criterios_item_mayores itcm4 ON itc_prov.f125_id_plan = itcm4.f106_id_plan AND itc_prov.f125_id_criterio_mayor = itcm4.f106_id
        LEFT JOIN t125_mc_items_criterios itc_tipinv ON it.f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
        LEFT JOIN t106_mc_criterios_item_mayores itcm8 ON itc_tipinv.f125_id_plan = itcm8.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm8.f106_id
        OUTER APPLY (
        SELECT TOP 1 cot.f212_precio FROM t212_mm_cotizaciones cot
        WHERE cot.f212_rowid_item_ext = vta.f9930_rowid_item_ext AND cot.f212_id_um IN ('UND')
            AND TRIM(itcm4.f106_id) = $codigoProveedor
            AND CAST(cot.f212_fecha_activacion AS DATE) <= CAST(vta.f9930_id_fecha_factura AS DATE)
        ORDER BY cot.f212_fecha_activacion DESC
        ) AS precio_vigente
        OUTER APPLY (
        SELECT TOP 1 cot.f212_precio FROM t212_mm_cotizaciones cot
        WHERE cot.f212_rowid_item_ext = vta.f9930_rowid_item_ext AND cot.f212_id_um IN ('UND')
            AND TRIM(itcm4.f106_id) = $codigoProveedor
        ORDER BY cot.f212_fecha_activacion ASC
        ) AS precio_minimo
        WHERE TRIM(itcm4.f106_id) = $codigoProveedor
        AND itcm8.f106_id_plan = '008' AND itcm8.f106_id = '0001'
        AND CAST(vta.f9930_id_fecha_factura AS DATE) BETWEEN $fechaDesde AND $fechaHasta
        )
        UNION ALL
        (
        SELECT
            t150.f150_id AS codigoCentroOperacion,
            t150.f150_descripcion AS nombreCentroOperacion,
            CAST(f461_id_fecha AS DATE) AS fecha,
            f470_rowid_item_ext,
            f120_id AS item,
            f121_id_barras_principal AS codigobarra,
            TRIM(f120_descripcion) AS descripcion,
            TRIM(f120_descripcion_corta) AS descripcionCorta,
            TRIM(f121_id_ext1_detalle) AS color,
            TRIM(f121_id_ext2_detalle) AS talla,
            TRIM(f120_referencia) AS referencia,
            TRIM(itcm4.f106_descripcion) AS nombreproveedor,
            TRIM(itcm4.f106_id) AS proveedor,
            CASE
                WHEN f470_ind_naturaleza = 1 THEN f470_cant_base * -1 * f470_factor
                ELSE f470_cant_base * f470_factor
            END AS unidades,
            ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio) AS precio_aplicado,
            CASE
                WHEN f470_ind_naturaleza = 1
                THEN f470_cant_base * ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio) * -1
                ELSE f470_cant_base * ISNULL(precio_vigente.f212_precio, precio_minimo.f212_precio)
            END AS total,
            f470_costo_prom_tot,
            f470_costo_prom_uni,
            f470_factor AS factor,
            f470_id_unidad_medida AS unidadmedida
        FROM t350_co_docto_contable t350_fact
        INNER JOIN t461_cm_docto_factura_venta ON f461_rowid_docto = t350_fact.f350_rowid
        INNER JOIN t470_cm_movto_invent t470 ON f470_rowid_docto_fact = f461_rowid_docto
        INNER JOIN t150_mc_bodegas t150 ON t150.f150_rowid = f470_rowid_bodega
        INNER JOIN t121_mc_items_extensiones ON f121_rowid = f470_rowid_item_ext
        INNER JOIN t120_mc_items ON f120_rowid = f121_rowid_item
        LEFT JOIN t125_mc_items_criterios itc_proveedor ON f120_rowid = itc_proveedor.f125_rowid_item AND itc_proveedor.f125_id_plan = '015'
        LEFT JOIN t106_mc_criterios_item_mayores itcm4 ON itc_proveedor.f125_id_plan = itcm4.f106_id_plan AND itc_proveedor.f125_id_criterio_mayor = itcm4.f106_id
        LEFT JOIN t125_mc_items_criterios itc_tipinv ON f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
        LEFT JOIN t106_mc_criterios_item_mayores itcm8 ON itc_tipinv.f125_id_plan = itcm8.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm8.f106_id
        LEFT JOIN t021_mm_tipos_documentos t021 ON t350_fact.f350_id_tipo_docto = t021.f021_id
        OUTER APPLY (
            SELECT TOP 1 cot.f212_precio FROM t212_mm_cotizaciones cot
            WHERE cot.f212_rowid_item_ext = f470_rowid_item_ext AND cot.f212_id_um IN ('UND')
                AND TRIM(itcm4.f106_id) = $codigoProveedor
                AND CAST(cot.f212_fecha_activacion AS DATE) <= CAST(f470_id_fecha AS DATE)
            ORDER BY cot.f212_fecha_activacion DESC
        ) AS precio_vigente
        OUTER APPLY (
            SELECT TOP 1 cot.f212_precio FROM t212_mm_cotizaciones cot
            WHERE cot.f212_rowid_item_ext = f470_rowid_item_ext AND cot.f212_id_um IN ('UND')
                AND TRIM(itcm4.f106_id) = $codigoProveedor
            ORDER BY cot.f212_fecha_activacion ASC
        ) AS precio_minimo
        WHERE TRIM(itcm4.f106_id) = $codigoProveedor
        AND itcm8.f106_id_plan = '008' AND itcm8.f106_id = '0001'
        AND CAST(t350_fact.f350_fecha AS DATE) BETWEEN $fechaDesde AND $fechaHasta
        AND t021.f021_id_cia = 7 AND t021.f021_id_flia_docto = '04' AND t021.f021_id_formato IS NOT NULL
        )
        SQL;

        $rows = Yii::$app->dbSiesa->createCommand($sql)->queryAll();

        $this->load($params);

        $rows = array_filter($rows, function ($row) {
            if ($this->item && stripos($row['item'], $this->item) === false) return false;
            if ($this->fecha && strpos($row['fecha'], $this->fecha) === false) return false;
            if ($this->descripcion && stripos($row['descripcion'], $this->descripcion) === false) return false;
            if ($this->codigobarra && stripos($row['codigobarra'], $this->codigobarra) === false) return false;
            if ($this->nombreCentroOperacion && stripos($row['nombreCentroOperacion'], $this->nombreCentroOperacion) === false) return false;
            return true;
        });

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 50],
            'sort'       => [
                'attributes'   => ['fecha', 'item'],
                'defaultOrder' => ['fecha' => SORT_ASC],
            ],
        ]);
    }
}
