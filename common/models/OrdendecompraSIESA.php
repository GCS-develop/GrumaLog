<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class OrdendecompraSIESA extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 't420_cm_oc_docto'; // Nombre de la tabla principal
    }

    public static function getDb()
    {
        return Yii::$app->dbSiesa; // Usa la conexión definida como dbsiesa
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    public static function obtenerDatosPorConsecutivo($idCia, $idCO, $idTipoDocumento, $consecutivo)
    {
        $sql = "
            SELECT 
                dct.f420_rowid AS id,
                dct.f420_fecha_ts_creacion AS fechaCreacion,
                dct.f420_ts,
                CAST(dct.f420_fecha AS DATE) AS fecha,
                dct.f420_id_cia AS idCia,
                dct.f420_id_co AS idCO, 
                cop.f285_descripcion AS CO,
                dct.f420_id_tipo_docto AS idTipoDocumento, 
                tdct.f021_descripcion AS tipoDocumento,
                dct.f420_consec_docto AS consecutivo, 
                dct.f420_id_grupo_clase_docto AS grupoClaseDcto, 
                dct.f420_ind_estado AS idEstadoDcto, 
                est.f054_descripcion AS estadoDcto,
                dct.f420_rowid_tercero_facturar, 
                dct.f420_id_sucursal_facturar AS sucursal,
                prv.f202_id_tipo_prov, 
                ter.f200_nit AS nit, 
                ter.f200_razon_social AS razonSocial,
                prv.f202_descripcion_sucursal AS descripcionSucursal,
                CASE 
                    WHEN ter.f200_id_tipo_ident = 'C' THEN 'CC'
                    WHEN ter.f200_id_tipo_ident = 'N' THEN 'NIT'
                    WHEN ter.f200_id_tipo_ident = 'E' THEN 'CE'
                    ELSE ter.f200_id_tipo_ident -- Opcional, por si el campo tiene otros valores inesperados
                END AS idTipoIdentificacion,
                tide.f203_descripcion AS tipoIdentificacion,
                cont.f015_contacto AS contactoProveedor,
                cont.f015_direccion1 AS direccionProveedor,
                ci.f013_descripcion AS ciudadProveedor,
                de.f012_descripcion AS deptoProveedor,
                pa.f011_descripcion AS paisProveedor,
                cont.f015_telefono AS telefonoProveedor,
                cont.f015_celular AS celularProveedor,
                cont.f015_email AS emailProveedor,
                (TRIM(prvcm.f220_id) + ' - ' + prvcm.f220_descripcion) AS mercancia,
                (TRIM(prvcm1.f220_id) + ' - ' + prvcm1.f220_descripcion) AS modelo,
                comp.f200_nit AS nitcomprador,
                comp.f200_razon_social AS comprador
            FROM t420_cm_oc_docto dct 
            INNER JOIN t285_co_centro_op cop 
                ON dct.f420_id_cia = cop.f285_id_cia AND dct.f420_id_co = cop.f285_id 
            INNER JOIN  t021_mm_tipos_documentos tdct 
                ON dct.f420_id_cia = tdct.f021_id_cia AND dct.f420_id_tipo_docto = tdct.f021_id
            INNER JOIN t054_mm_estados est 
                ON dct.f420_id_grupo_clase_docto = est.f054_id_grupo_clase_docto 
                AND dct.f420_ind_estado = est.f054_id
            INNER JOIN t202_mm_proveedores prv 
                ON dct.f420_rowid_tercero_facturar = prv.f202_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prv.f202_id_sucursal
            INNER JOIN t200_mm_terceros ter 
                ON prv.f202_rowid_tercero = ter.f200_rowid
            LEFT JOIN t015_mm_contactos cont 
                ON ter.f200_rowid_contacto = cont.f015_rowid 
            LEFT JOIN t200_mm_terceros comp 
                ON dct.f420_rowid_tercero_sol_comp = comp.f200_rowid 
            LEFT JOIN t013_mm_ciudades ci 
                ON cont.f015_id_ciudad = ci.f013_id 
                AND cont.f015_id_depto = ci.f013_id_depto 
                AND cont.f015_id_pais = ci.f013_id_pais
            LEFT JOIN t012_mm_deptos de 
                ON ci.f013_id_pais = de.f012_id_pais 
                AND ci.f013_id_depto = de.f012_id
            LEFT JOIN t011_mm_paises pa 
                ON ci.f013_id_pais = pa.f011_id
            LEFT JOIN t203_mm_tipo_ident tide 
                ON ter.f200_id_cia = tide.f203_id_cia 
                AND ter.f200_id_tipo_ident = f203_id
            LEFT JOIN t221_mm_criterios_proveedores prvc_mercancia
                ON dct.f420_rowid_tercero_facturar = prvc_mercancia.f221_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prvc_mercancia.f221_id_sucursal
                AND prvc_mercancia.f221_id_plan_criterios = '001'
            LEFT JOIN t220_mm_criterios_mayores_prov prvcm 
                ON prvc_mercancia.f221_id_plan_criterios = prvcm.f220_id_plan 
                AND prvc_mercancia.f221_id_criterio_mayor = prvcm.f220_id
            LEFT JOIN t221_mm_criterios_proveedores prvc_modelo
                ON dct.f420_rowid_tercero_facturar = prvc_modelo.f221_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prvc_modelo.f221_id_sucursal
                AND prvc_modelo.f221_id_plan_criterios = '003'
            LEFT JOIN t220_mm_criterios_mayores_prov prvcm1 
                ON prvc_modelo.f221_id_plan_criterios = prvcm1.f220_id_plan 
                AND prvc_modelo.f221_id_criterio_mayor = prvcm1.f220_id
            WHERE dct.f420_id_cia = :idCia
                AND dct.f420_id_co = :idCO
                AND dct.f420_id_tipo_docto = :idTipoDocumento
                AND dct.f420_consec_docto = :consecutivo
        ";

        return self::getDb()->createCommand($sql)
            ->bindValue(':idCia', $idCia)
            ->bindValue(':idCO', $idCO)
            ->bindValue(':idTipoDocumento', $idTipoDocumento)
            ->bindValue(':consecutivo', $consecutivo)
            ->queryAll();
    }

    /**
     * Método para ejecutar el query especificado y obtener los resultados.
     *
     * @param string $idCia
     * @param string $fechaInicio Fecha y hora de inicio en formato 'Y-m-d H:i:s'.
     * @param string $fechaFin Fecha y hora de fin en formato 'Y-m-d H:i:s'.
     * @return array Resultados del query.
     */
    public static function obtenerDatosPorRangoFechas($idCia, $fechaInicio, $fechaFin)
    {
        $sql = "
            SELECT 
                dct.f420_rowid AS id,
                dct.f420_fecha_ts_creacion AS fechaCreacion,
                dct.f420_ts,
                CAST(dct.f420_fecha AS DATE) AS fecha,
                dct.f420_id_cia AS idCia,
                dct.f420_id_co AS idCO, 
                cop.f285_descripcion AS CO,
                dct.f420_id_tipo_docto AS idTipoDocumento, 
                tdct.f021_descripcion AS tipoDocumento,
                dct.f420_consec_docto AS consecutivo, 
                dct.f420_id_grupo_clase_docto AS grupoClaseDcto, 
                dct.f420_ind_estado AS idEstadoDcto, 
                est.f054_descripcion AS estadoDcto,
                dct.f420_rowid_tercero_facturar, 
                dct.f420_id_sucursal_facturar AS sucursal,
                prv.f202_id_tipo_prov, 
                ter.f200_nit AS nit, 
                ter.f200_razon_social AS razonSocial,
                prv.f202_descripcion_sucursal AS descripcionSucursal,
                CASE 
                    WHEN ter.f200_id_tipo_ident = 'C' THEN 'CC'
                    WHEN ter.f200_id_tipo_ident = 'N' THEN 'NIT'
                    WHEN ter.f200_id_tipo_ident = 'E' THEN 'CE'
                    ELSE ter.f200_id_tipo_ident -- Opcional, por si el campo tiene otros valores inesperados
                END AS idTipoIdentificacion,
                tide.f203_descripcion AS tipoIdentificacion,
                cont.f015_contacto AS contactoProveedor,
                cont.f015_direccion1 AS direccionProveedor,
                ci.f013_descripcion AS ciudadProveedor,
                de.f012_descripcion AS deptoProveedor,
                pa.f011_descripcion AS paisProveedor,
                cont.f015_telefono AS telefonoProveedor,
                cont.f015_celular AS celularProveedor,
                cont.f015_email AS emailProveedor,
                (TRIM(prvcm.f220_id) + ' - ' + prvcm.f220_descripcion) AS mercancia,
                (TRIM(prvcm1.f220_id) + ' - ' + prvcm1.f220_descripcion) AS modelo,
                comp.f200_nit AS nitcomprador,
                comp.f200_razon_social AS comprador
            FROM t420_cm_oc_docto dct 
            INNER JOIN t285_co_centro_op cop 
                ON dct.f420_id_cia = cop.f285_id_cia AND dct.f420_id_co = cop.f285_id 
            INNER JOIN  t021_mm_tipos_documentos tdct 
                ON dct.f420_id_cia = tdct.f021_id_cia AND dct.f420_id_tipo_docto = tdct.f021_id
            INNER JOIN t054_mm_estados est 
                ON dct.f420_id_grupo_clase_docto = est.f054_id_grupo_clase_docto 
                AND dct.f420_ind_estado = est.f054_id
            INNER JOIN t202_mm_proveedores prv 
                ON dct.f420_rowid_tercero_facturar = prv.f202_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prv.f202_id_sucursal
            INNER JOIN t200_mm_terceros ter 
                ON prv.f202_rowid_tercero = ter.f200_rowid
            LEFT JOIN t015_mm_contactos cont 
                ON ter.f200_rowid_contacto = cont.f015_rowid

            LEFT JOIN t200_mm_terceros comp 
                ON dct.f420_rowid_tercero_sol_comp = comp.f200_rowid

            LEFT JOIN t013_mm_ciudades ci 
                ON cont.f015_id_ciudad = ci.f013_id 
                AND cont.f015_id_depto = ci.f013_id_depto 
                AND cont.f015_id_pais = ci.f013_id_pais
            LEFT JOIN t012_mm_deptos de 
                ON ci.f013_id_pais = de.f012_id_pais 
                AND ci.f013_id_depto = de.f012_id
            LEFT JOIN t011_mm_paises pa 
                ON ci.f013_id_pais = pa.f011_id
            LEFT JOIN t203_mm_tipo_ident tide 
                ON ter.f200_id_cia = tide.f203_id_cia 
                AND ter.f200_id_tipo_ident = f203_id
            LEFT JOIN t221_mm_criterios_proveedores prvc_mercancia
                ON dct.f420_rowid_tercero_facturar = prvc_mercancia.f221_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prvc_mercancia.f221_id_sucursal
                AND prvc_mercancia.f221_id_plan_criterios = '001'
            LEFT JOIN t220_mm_criterios_mayores_prov prvcm 
                ON prvc_mercancia.f221_id_plan_criterios = prvcm.f220_id_plan 
                AND prvc_mercancia.f221_id_criterio_mayor = prvcm.f220_id
            LEFT JOIN t221_mm_criterios_proveedores prvc_modelo
                ON dct.f420_rowid_tercero_facturar = prvc_modelo.f221_rowid_tercero 
                AND dct.f420_id_sucursal_facturar = prvc_modelo.f221_id_sucursal
                AND prvc_modelo.f221_id_plan_criterios = '003'
            LEFT JOIN t220_mm_criterios_mayores_prov prvcm1 
                ON prvc_modelo.f221_id_plan_criterios = prvcm1.f220_id_plan 
                AND prvc_modelo.f221_id_criterio_mayor = prvcm1.f220_id
            WHERE dct.f420_id_cia = :idCia
                AND CONVERT(VARCHAR, dct.f420_fecha_ts_creacion, 120) 
                BETWEEN :fechaInicio AND :fechaFin
            ORDER BY dct.f420_fecha_ts_creacion
        ";

        /*return Yii::$app->dbsiesa->createCommand($sql)
            ->bindValue(':fechaInicio', $fechaInicio)
            ->bindValue(':fechaFin', $fechaFin)
            ->queryAll();
        */
        return self::getDb()->createCommand($sql)
            ->bindValue(':idCia', $idCia)
            ->bindValue(':fechaInicio', $fechaInicio)
            ->bindValue(':fechaFin', $fechaFin)
            ->queryAll();
    }

    public static function obtenerDatosDetalleOC($idCia, $idDocumento)
    {
        $sql = "
            SELECT 
                mvt.f421_rowid_item_ext
                , itx.f121_rowid_item
                , it.f120_id AS item
                , bar.f131_id AS codigoBarras
                , TRIM(it.f120_referencia) AS referencia
                , TRIM(it.f120_descripcion) AS descripcion
                , TRIM(it.f120_descripcion_corta) AS descripcionCorta
                , itx.f121_ind_estado AS idEstadoItem
                ,
                CASE 
                    WHEN itx.f121_ind_estado = 1 THEN 'ACTIVO'
                    WHEN itx.f121_ind_estado = 0 THEN 'INACTIVO'
                    WHEN itx.f121_ind_estado = 2 THEN 'BLOQUEADO'
                    ELSE 'DESCONOCIDO' -- Opcional, por si el campo tiene otros valores inesperados
                END AS estadoItem
                -- , itx.f121_id_ext1_detalle AS color
                -- , itx.f121_id_ext2_detalle AS talla 

                , TRIM(col.f117_id) AS idColor
                , TRIM(col.f117_descripcion) AS color
                , TRIM(tl.f119_id) AS idTalla
                , TRIM(tl.f119_descripcion) AS talla

                , TRIM(itcm.f106_id) AS idCategoria
                , TRIM(itcm.f106_descripcion) AS categoria
                , TRIM(itcm1.f106_id) AS idSubcategoria
                , TRIM(itcm1.f106_descripcion) AS subcategoria
                , TRIM(itcm2.f106_id) AS idProducto
                , TRIM(itcm2.f106_descripcion) AS producto
                , TRIM(itcm3.f106_id) AS idMarca
                , TRIM(itcm3.f106_descripcion) AS marca
                , TRIM(itcm4.f106_id) AS idProveedor
                , TRIM(itcm4.f106_descripcion) AS proveedor

                , it.f120_id_unidad_empaque AS unidadEmpaque
                , it.f120_id_unidad_orden AS unidadOrden
                , mvt.f421_id_unidad_medida 
                , mvt.f421_cant_pedida  AS cantidadPedida
                , mvt.f421_cant_entrada AS cantidadEntrada
                , (mvt.f421_cant_pedida - mvt.f421_cant_entrada) AS cantidadPendiente
                , CAST(mvt.f421_fecha AS DATE) AS fecha
                , CAST(mvt.f421_fecha_entrega AS DATE) AS fechaEntrega
                , bod.f150_id AS bodega
                , mvt.f421_rowid AS codigointernomovto
                FROM t421_cm_oc_movto mvt
                INNER JOIN t420_cm_oc_docto dct 
                ON mvt.f421_id_cia = dct.f420_id_cia AND  mvt.f421_rowid_oc_docto = dct.f420_rowid

                INNER JOIN t121_mc_items_extensiones itx ON mvt.f421_rowid_item_ext = itx.f121_rowid
                INNER JOIN t120_mc_items  it ON itx.f121_rowid_item = it.f120_rowid

                LEFT JOIN t117_mc_extensiones1_detalle col 
                ON mvt.f421_id_cia = col.f117_id_cia AND itx.f121_id_extension1 = col.f117_id_extension1 AND 
                itx.f121_id_ext1_detalle = col.f117_id

                LEFT JOIN t119_mc_extensiones2_detalle tl 
                ON mvt.f421_id_cia = tl.f119_id_cia AND itx.f121_id_extension2 = tl.f119_id_extension2 
                AND itx.f121_id_ext2_detalle = tl.f119_id

                LEFT JOIN t125_mc_items_criterios itc_categoria 
                ON it.f120_rowid = itc_categoria.f125_rowid_item AND itc_categoria.f125_id_plan = '001'
                LEFT JOIN t106_mc_criterios_item_mayores itcm 
                ON itc_categoria.f125_id_plan = itcm.f106_id_plan AND itc_categoria.f125_id_criterio_mayor = itcm.f106_id

                LEFT JOIN t125_mc_items_criterios itc_subcategoria 
                ON it.f120_rowid = itc_subcategoria.f125_rowid_item AND itc_subcategoria.f125_id_plan = '002'
                LEFT JOIN t106_mc_criterios_item_mayores itcm1 
                ON itc_subcategoria.f125_id_plan = itcm1.f106_id_plan AND itc_subcategoria.f125_id_criterio_mayor = itcm1.f106_id

                LEFT JOIN t125_mc_items_criterios itc_producto 
                ON it.f120_rowid = itc_producto.f125_rowid_item AND itc_producto.f125_id_plan = '005'
                LEFT JOIN t106_mc_criterios_item_mayores itcm2 
                ON itc_producto.f125_id_plan = itcm2.f106_id_plan AND itc_producto.f125_id_criterio_mayor = itcm2.f106_id

                LEFT JOIN t125_mc_items_criterios itc_marca 
                ON it.f120_rowid = itc_marca.f125_rowid_item AND itc_marca.f125_id_plan = '014'
                LEFT JOIN t106_mc_criterios_item_mayores itcm3 
                ON itc_marca.f125_id_plan = itcm3.f106_id_plan AND itc_marca.f125_id_criterio_mayor = itcm3.f106_id

                LEFT JOIN t125_mc_items_criterios itc_proveedor
                ON it.f120_rowid = itc_proveedor.f125_rowid_item AND itc_proveedor.f125_id_plan = '015'
                LEFT JOIN t106_mc_criterios_item_mayores itcm4 
                ON itc_proveedor.f125_id_plan = itcm4.f106_id_plan AND itc_proveedor.f125_id_criterio_mayor = itcm4.f106_id

                LEFT JOIN t131_mc_items_barras bar ON itx.f121_id_barras_principal = bar.f131_id
                LEFT JOIN t150_mc_bodegas bod ON mvt.f421_id_cia = bod.f150_id_cia AND mvt.f421_rowid_bodega = bod.f150_rowid  

                WHERE dct.f420_id_cia = :idCia AND dct.f420_rowid = :idDocumento
        ";

        return self::getDb()->createCommand($sql)
            ->bindValue(':idCia', $idCia)
            ->bindValue(':idDocumento', $idDocumento)
            ->queryAll();
    }

    public static function obtenerDatosPrecioVenta ($codigobarras, $fechaactivacion, $codigolistaprecios){
        $sql = "
            SELECT TOP 1 ipre.f126_rowid, ipre.f126_id_cia, f126_rowid_item, FORMAT(ipre.f126_fecha_activacion, 'yyyy-MM-dd') AS fecha_activacion, ipre.f126_precio
                FROM t126_mc_items_precios ipre
                INNER JOIN t120_mc_items  it ON ipre.f126_rowid_item = it.f120_rowid
                INNER JOIN t121_mc_items_extensiones itx ON itx.f121_rowid_item = it.f120_rowid
                LEFT JOIN t131_mc_items_barras bar ON itx.f121_id_barras_principal = bar.f131_id
                WHERE ipre.f126_id_lista_precio = :codigolistaprecios AND itx.f121_id_barras_principal IN (:codigobarras) 
                AND FORMAT(ipre.f126_fecha_activacion, 'yyyy-MM-dd') <= :fechaactivacion
                ORDER BY ipre.f126_fecha_activacion DESC     
        ";
        
        return self::getDb()->createCommand($sql)
            ->bindValue('codigolistaprecios', $codigolistaprecios)
            ->bindValue(':codigobarras', $codigobarras)
            ->bindValue(':fechaactivacion', $fechaactivacion)
            ->queryAll();
    }

    public static function obtenerDatosDocumento ($tipodocumento, $numerodocumento, $tipomovimiento = null){

        if ($tipomovimiento){
            $pattern = $tipomovimiento . $tipodocumento . $numerodocumento;
        }else{
            $pattern = $tipodocumento . $numerodocumento;
        }

        $sql = "
            SELECT TOP 1
            f350_rowid, 
            f350_id_cia, 
            f350_id_co, 
            f350_id_tipo_docto,
            f350_consec_docto, 
            :tipodocumento AS tipoDocumento, 
            :numerodocumento AS numeroDocumento
            FROM t350_co_docto_contable
            WHERE f350_id_tipo_docto = :tipodocumento
            AND CHARINDEX(:pattern, f350_notas) > 0
            AND f350_ind_estado = 1;
        ";

        /*$sql = "
            SELECT TOP 1 
            f350_rowid, 
            f350_id_cia, 
            f350_id_co, 
            f350_id_tipo_docto, 
            f350_consec_docto, 
            :tipodocumento AS tipoDocumento,
            :numerodocumento AS numeroDocumento
            FROM
            t350_co_docto_contable
            WHERE f350_id_tipo_docto = :tipodocumento
            AND f350_notas LIKE CAST('%" . $pattern . "%' AS NVARCHAR) " .    
            "AND f350_ind_estado = 1;
        ";*/

        return self::getDb()->createCommand($sql)
        ->bindValue('tipodocumento', $tipodocumento)
        ->bindValue(':numerodocumento', $numerodocumento)
        ->bindValue(':pattern', $pattern)
        ->queryAll();
        
    }

    public static function obtenerDatosItem ($item, $color, $talla){
        $sql = "
            SELECT 
                bar.f131_id AS codigoBarras
                , itx.f121_id_barras_principal AS codigoBarrasPrincipal
                , it.f120_id_cia, it.f120_id AS item
                , it.f120_referencia AS referencia
                , it.f120_descripcion AS descripcion
                , it.f120_descripcion_corta AS descripcionCorta
                , it.f120_id_unidad_inventario
                , it.f120_id_unidad_empaque AS unidadEmpaque
                , it.f120_id_unidad_orden AS unidadOrden
                , itx.f121_id_ext1_detalle AS idColor
                , col.f117_descripcion AS color
                , itx.f121_id_ext2_detalle AS idTalla
                , tl.f119_descripcion AS talla
                , TRIM(itcm.f106_id) AS idCategoria
                , TRIM(itcm.f106_descripcion) AS categoria
                , TRIM(itcm1.f106_id) AS idSubcategoria
                , TRIM(itcm1.f106_descripcion) AS subcategoria
                , TRIM(itcm2.f106_id) AS idProducto
                , TRIM(itcm2.f106_descripcion) AS producto
                , TRIM(itcm3.f106_id) AS idMarca
                , TRIM(itcm3.f106_descripcion) AS marca
                , TRIM(itcm4.f106_id) AS idProveedor
                , TRIM(itcm4.f106_descripcion) AS proveedor
                , itx.f121_ind_estado AS idEstadoItem
                ,
                CASE 
                WHEN itx.f121_ind_estado = 1 THEN 'ACTIVO'
                WHEN itx.f121_ind_estado = 0 THEN 'INACTIVO'
                WHEN itx.f121_ind_estado = 2 THEN 'BLOQUEADO'
                ELSE 'DESCONOCIDO' -- Opcional, por si el campo tiene otros valores inesperados
                END AS estadoItem
                FROM t120_mc_items  it                 
                INNER JOIN t121_mc_items_extensiones itx ON itx.f121_rowid_item = it.f120_rowid
                LEFT JOIN t131_mc_items_barras bar ON itx.f121_rowid = bar.f131_rowid_item_ext
                -- itx.f121_id_barras_principal = bar.f131_id

                LEFT JOIN t117_mc_extensiones1_detalle col 
                ON it.f120_id_cia = col.f117_id_cia AND itx.f121_id_extension1 = col.f117_id_extension1 AND 
                itx.f121_id_ext1_detalle = col.f117_id

                LEFT JOIN t119_mc_extensiones2_detalle tl 
                ON it.f120_id_cia = tl.f119_id_cia AND itx.f121_id_extension2 = tl.f119_id_extension2 
                AND itx.f121_id_ext2_detalle = tl.f119_id

                LEFT JOIN t125_mc_items_criterios itc_categoria 
                ON it.f120_rowid = itc_categoria.f125_rowid_item AND itc_categoria.f125_id_plan = '001'
                LEFT JOIN t106_mc_criterios_item_mayores itcm 
                ON itc_categoria.f125_id_plan = itcm.f106_id_plan AND itc_categoria.f125_id_criterio_mayor = itcm.f106_id

                LEFT JOIN t125_mc_items_criterios itc_subcategoria 
                ON it.f120_rowid = itc_subcategoria.f125_rowid_item AND itc_subcategoria.f125_id_plan = '002'
                LEFT JOIN t106_mc_criterios_item_mayores itcm1 
                ON itc_subcategoria.f125_id_plan = itcm1.f106_id_plan AND itc_subcategoria.f125_id_criterio_mayor = itcm1.f106_id

                LEFT JOIN t125_mc_items_criterios itc_producto 
                ON it.f120_rowid = itc_producto.f125_rowid_item AND itc_producto.f125_id_plan = '005'
                LEFT JOIN t106_mc_criterios_item_mayores itcm2 
                ON itc_producto.f125_id_plan = itcm2.f106_id_plan AND itc_producto.f125_id_criterio_mayor = itcm2.f106_id

                LEFT JOIN t125_mc_items_criterios itc_marca 
                ON it.f120_rowid = itc_marca.f125_rowid_item AND itc_marca.f125_id_plan = '014'
                LEFT JOIN t106_mc_criterios_item_mayores itcm3 
                ON itc_marca.f125_id_plan = itcm3.f106_id_plan AND itc_marca.f125_id_criterio_mayor = itcm3.f106_id

                LEFT JOIN t125_mc_items_criterios itc_proveedor
                ON it.f120_rowid = itc_proveedor.f125_rowid_item AND itc_proveedor.f125_id_plan = '015'
                LEFT JOIN t106_mc_criterios_item_mayores itcm4 
                ON itc_proveedor.f125_id_plan = itcm4.f106_id_plan AND itc_proveedor.f125_id_criterio_mayor = itcm4.f106_id

                WHERE it.f120_id = :item AND itx.f121_id_ext1_detalle = :color AND itx.f121_id_ext2_detalle = :talla;
        ";

        return self::getDb()->createCommand($sql)
            ->bindValue('item', $item)
            ->bindValue('color', $color)
            ->bindValue('talla', $talla)
            ->queryAll();
    }

}
