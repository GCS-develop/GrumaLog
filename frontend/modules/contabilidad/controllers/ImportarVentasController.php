<?php

namespace app\commands;

use yii\console\Controller;
use Yii;

class ImportarVentasController extends Controller
{
    public function actionIndex()
    {
        $dbSiesa = Yii::$app->dbSiesa;

        // Aquí insertas tu query completo
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
        -- (el resto de joins aquí)
        WHERE TRIM(itcm4.f106_id) = '0290'
        -- puedes añadir filtros de fechas si deseas
SQL;

        $ventas = $dbSiesa->createCommand($sql)->queryAll();

        $dbLocal = Yii::$app->db;

        foreach ($ventas as $venta) {
            $dbLocal->createCommand()->insert('ventasimportadas', [
                'codigoCentroOperacion' => $venta['codigoCentroOperacion'],
                'nombreCentroOperacion' => $venta['nombreCentroOperacion'],
                'fecha' => $venta['fecha'],
                'rowid_item_ext' => $venta['f9930_rowid_item_ext'],
                'item' => $venta['item'],
                'codigobarra' => $venta['codigobarra'],
                'descripcion' => $venta['descripcion'],
                'descripcionCorta' => $venta['descripcionCorta'],
                'color' => $venta['color'],
                'talla' => $venta['talla'],
                'referencia' => $venta['referencia'],
                'nombreproveedor' => $venta['nombreproveedor'],
                'proveedor' => $venta['proveedor'],
                'unidades' => $venta['unidades'],
                'precio_aplicado' => $venta['precio_aplicado'],
                'total' => $venta['total'],
                'costo_prom_tot' => $venta['f9930_costo_prom_tot'],
                'costo_prom_mp' => $venta['f9930_costo_prom_mp'],
                'factor' => $venta['factor'],
                'unidadmedida' => $venta['unidadmedida'],
            ])->execute();
        }

        echo "Importación completa: " . count($ventas) . " registros\n";
    }
}
