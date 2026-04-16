<?php

namespace frontend\modules\programacion\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;

class ConsolidadoController extends Controller
{
    public function actionIndex()
    {
        $filtroBodega = Yii::$app->request->get('bodega', '');
        $filtroZona   = Yii::$app->request->get('zona', '');
        $filtroItem   = Yii::$app->request->get('item', '');
        $filtroDesc   = Yii::$app->request->get('descripcion', '');

        $where  = ' WHERE inv.existencia > 0';
        $params = [];

        if ($filtroBodega !== '') {
            $where .= ' AND b.codigo = :bodega';
            $params[':bodega'] = $filtroBodega;
        }
        if ($filtroZona !== '') {
            $where .= ' AND CAST(b.zona AS NVARCHAR(20)) = :zona';
            $params[':zona'] = $filtroZona;
        }
        if ($filtroItem !== '') {
            $where .= ' AND CAST(it.item AS NVARCHAR(20)) LIKE :item';
            $params[':item'] = '%' . $filtroItem . '%';
        }
        if ($filtroDesc !== '') {
            $where .= ' AND it.descripcion LIKE :desc';
            $params[':desc'] = '%' . $filtroDesc . '%';
        }

        $sqlPrincipal = "
            SELECT
                CAST(b.zona AS NVARCHAR(20))                                    AS zona,
                b.codigo                                                         AS codigoBodega,
                b.nombre                                                         AS nombreBodega,
                it.item,
                it.descripcion,
                COALESCE(col.codigo, '')                                         AS color,
                COALESCE(tal.codigo, '')                                         AS talla,
                inv.codigoBarras,
                inv.existencia,
                CONVERT(NVARCHAR(16), inv.fechaUltimaActualizacion, 120)        AS fechaActualizacion
            FROM inventario inv
            INNER JOIN item    it  ON it.id  = inv.idItem
            INNER JOIN bodegas b   ON b.codigo = inv.codigoBodega
            LEFT  JOIN color   col ON col.id = it.idColor
            LEFT  JOIN talla   tal ON tal.id = it.idTalla
            $where
            ORDER BY b.zona, b.codigo, it.item, col.codigo, tal.codigo
        ";

        // Totales por bodega
        $sqlTotales = "
            SELECT
                b.codigo                    AS codigoBodega,
                b.nombre                    AS nombreBodega,
                CAST(b.zona AS NVARCHAR(20)) AS zona,
                SUM(inv.existencia)          AS totalUnidades,
                COUNT(DISTINCT inv.idItem)   AS totalItems
            FROM inventario inv
            INNER JOIN bodegas b ON b.codigo = inv.codigoBodega
            $where
            GROUP BY b.codigo, b.nombre, b.zona
            ORDER BY b.zona, b.codigo
        ";

        // Listas para los dropdowns de filtro
        $listaBodegas = Yii::$app->db->createCommand("
            SELECT DISTINCT b.codigo, b.nombre
            FROM inventario inv
            INNER JOIN bodegas b ON b.codigo = inv.codigoBodega
            WHERE inv.existencia > 0
            ORDER BY b.codigo
        ")->queryAll();

        $listaZonas = Yii::$app->db->createCommand("
            SELECT DISTINCT CAST(b.zona AS NVARCHAR(20)) AS zona
            FROM inventario inv
            INNER JOIN bodegas b ON b.codigo = inv.codigoBodega
            WHERE inv.existencia > 0 AND b.zona IS NOT NULL
            ORDER BY zona
        ")->queryColumn();

        $data    = Yii::$app->db->createCommand($sqlPrincipal, $params)->queryAll();
        $totales = Yii::$app->db->createCommand($sqlTotales,   $params)->queryAll();

        $dataProvider = new ArrayDataProvider([
            'allModels'  => $data,
            'pagination' => ['pageSize' => 100],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'totales'      => $totales,
            'listaBodegas' => $listaBodegas,
            'listaZonas'   => $listaZonas,
            'filtros'      => [
                'bodega'      => $filtroBodega,
                'zona'        => $filtroZona,
                'item'        => $filtroItem,
                'descripcion' => $filtroDesc,
            ],
        ]);
    }
}
