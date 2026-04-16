<?php

namespace frontend\modules\compras\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class PortalExistenciasSearch extends Model
{
    public $item;
    public $descripcion;
    public $nombreCentroOperacion;
    public $codigobarra;

    public function rules()
    {
        return [
            [['item', 'descripcion', 'codigobarra', 'nombreCentroOperacion'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'proveedor'              => 'Cod. Proveedor',
            'nombreproveedor'        => 'Proveedor',
            'nombreCentroOperacion'  => 'Desc. Bodega',
            'codigoCentroOperacion'  => 'Cod. Bodega',
            'codigobarra'            => 'Código Barras',
            'item'                   => 'Item',
            'nombrecategoria'        => 'Categoría',
            'categoria'              => 'Cod. Categoría',
            'nombresubcategoria'     => 'Subcategoría',
            'subcategoria'           => 'Cod. Subcategoría',
            'nombremarca'            => 'Marca',
            'marca'                  => 'Cod. Marca',
        ];
    }

    public function search($params, $codigo, $item = null)
    {
        $codigoProveedor = Yii::$app->dbSiesa->quoteValue($codigo);

        $condicionItem = '';
        if (!empty($item)) {
            $item          = Yii::$app->dbSiesa->quoteValue($item);
            $condicionItem = "AND f120_id = $item";
        }

        $sql = <<<SQL
        SELECT proveedor, nombreproveedor, codigoCentroOperacion, nombreCentroOperacion,
               codigobarra, item, descripcion, referencia, color, talla,
               categoria, nombrecategoria, subcategoria, nombresubcategoria,
               marca, nombremarca, SUM(Cantidad) as Existencia
        FROM (
            SELECT
                b.f150_id AS codigoCentroOperacion,
                b.f150_descripcion AS nombreCentroOperacion,
                d.f121_id_barras_principal AS codigobarra,
                f120_id AS item,
                TRIM(it.f120_descripcion) AS descripcion,
                TRIM(it.f120_descripcion_corta) AS descripcionCorta,
                d.f121_rowid_item AS Rowid_item,
                TRIM(f121_id_ext1_detalle) AS color,
                TRIM(f121_id_ext2_detalle) AS talla,
                TRIM(it.f120_referencia) AS referencia,
                TRIM(itcm5.f106_descripcion) AS nombreproveedor,
                TRIM(itcm5.f106_id) AS proveedor,
                TRIM(itcm1.f106_descripcion) AS nombrecategoria,
                TRIM(itcm1.f106_id) AS categoria,
                TRIM(itcm2.f106_descripcion) AS nombresubcategoria,
                TRIM(itcm2.f106_id) AS subcategoria,
                TRIM(itcm3.f106_descripcion) AS nombremarca,
                TRIM(itcm3.f106_id) AS marca,
                CASE WHEN a.f470_ind_naturaleza = 2 THEN -a.f470_cant_1 ELSE a.f470_cant_1 END AS Cantidad
            FROM t470_cm_movto_invent a
            INNER JOIN t150_mc_bodegas b ON a.f470_rowid_bodega = b.f150_rowid
            LEFT JOIN t350_co_docto_contable c ON a.f470_rowid_docto = c.f350_rowid
            INNER JOIN t121_mc_items_extensiones d ON a.f470_rowid_item_ext = d.f121_rowid
            INNER JOIN t120_mc_items it ON f121_rowid_item = it.f120_rowid
            INNER JOIN t145_mc_conceptos e ON a.f470_id_concepto = e.f145_id
            LEFT JOIN t125_mc_items_criterios itc_categoria ON it.f120_rowid = itc_categoria.f125_rowid_item AND itc_categoria.f125_id_plan = '001'
            LEFT JOIN t106_mc_criterios_item_mayores itcm1 ON itc_categoria.f125_id_plan = itcm1.f106_id_plan AND itc_categoria.f125_id_criterio_mayor = itcm1.f106_id
            LEFT JOIN t125_mc_items_criterios itc_subcategoria ON it.f120_rowid = itc_subcategoria.f125_rowid_item AND itc_subcategoria.f125_id_plan = '002'
            LEFT JOIN t106_mc_criterios_item_mayores itcm2 ON itc_subcategoria.f125_id_plan = itcm2.f106_id_plan AND itc_subcategoria.f125_id_criterio_mayor = itcm2.f106_id
            LEFT JOIN t125_mc_items_criterios itc_marca ON it.f120_rowid = itc_marca.f125_rowid_item AND itc_marca.f125_id_plan = '014'
            LEFT JOIN t106_mc_criterios_item_mayores itcm3 ON itc_marca.f125_id_plan = itcm3.f106_id_plan AND itc_marca.f125_id_criterio_mayor = itcm3.f106_id
            LEFT JOIN t125_mc_items_criterios itc_tipinv ON it.f120_rowid = itc_tipinv.f125_rowid_item AND itc_tipinv.f125_id_plan = '008'
            LEFT JOIN t106_mc_criterios_item_mayores itcm4 ON itc_tipinv.f125_id_plan = itcm4.f106_id_plan AND itc_tipinv.f125_id_criterio_mayor = itcm4.f106_id
            LEFT JOIN t125_mc_items_criterios itc_proveedor ON it.f120_rowid = itc_proveedor.f125_rowid_item AND itc_proveedor.f125_id_plan = '015'
            LEFT JOIN t106_mc_criterios_item_mayores itcm5 ON itc_proveedor.f125_id_plan = itcm5.f106_id_plan AND itc_proveedor.f125_id_criterio_mayor = itcm5.f106_id
            WHERE a.f470_id_fecha <= GETDATE()
                AND a.f470_ind_estado_cm != 2
                AND TRIM(itcm5.f106_id) = $codigoProveedor
                $condicionItem
                AND itcm4.f106_id_plan = '008' AND itcm4.f106_id = '0001'
        ) AS MovimientosPaginados
        GROUP BY proveedor, nombreproveedor, codigoCentroOperacion, nombreCentroOperacion,
                 codigobarra, item, descripcion, referencia, color, talla,
                 categoria, nombrecategoria, subcategoria, nombresubcategoria,
                 marca, nombremarca
        HAVING SUM(Cantidad) > 0
        SQL;

        $rows = Yii::$app->dbSiesa->createCommand($sql)->queryAll();

        $this->load($params);

        $rows = array_filter($rows, function ($row) {
            if ($this->item && stripos($row['item'], $this->item) === false) return false;
            if ($this->descripcion && stripos($row['descripcion'], $this->descripcion) === false) return false;
            if ($this->codigobarra && stripos($row['codigobarra'], $this->codigobarra) === false) return false;
            if ($this->nombreCentroOperacion && stripos($row['nombreCentroOperacion'], $this->nombreCentroOperacion) === false) return false;
            return true;
        });

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 50],
            'sort'       => [
                'attributes'   => ['item', 'color', 'talla'],
                'defaultOrder' => ['item' => SORT_ASC],
            ],
        ]);
    }
}
