<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;

class GrumascanReporteSearch extends Model
{
    public $tienda; // filtro por código normalizado (ej: 075 o NA010)
    public $item;
    public $color;
    public $talla;

    public function rules()
    {
        return [
            [['tienda', 'item', 'color', 'talla'], 'safe'],
        ];
    }

    /**
     * Consolidado Conteo (solo idestado=1) vs Inventario por Tienda+SKU
     * SKU = item + (idColor texto) + (idTalla texto)
     * Conteo convertido a unidades con unidadEmpaque.equivalencia.
     * Inventario: 1 sola existencia por SKU (MAX(existencia)) por tienda.
     *
     * @return array{dataProvider: SqlDataProvider, resumenTiendas: array<int,array<string,mixed>>}
     */
    public function searchConsolidadoConteoVsInventario($params)
    {
        $this->load($params);

        // =============================
        // Helper SQL: normalizar tienda
        // - si es numérica: pad a 3 dígitos (075)
        // - si es alfanumérica: se deja tal cual (NA010)
        // =============================
        $normBodega = function (string $sqlExpr) {
            // SQL Server: TRY_CONVERT(int, ...) para detectar si es numérico
            return new Expression("
                CASE
                    WHEN TRY_CONVERT(int, LTRIM(RTRIM($sqlExpr))) IS NOT NULL
                        THEN RIGHT('000' + LTRIM(RTRIM($sqlExpr)), 3)
                    ELSE LTRIM(RTRIM($sqlExpr))
                END
            ");
        };

        // =============================
        // 1) Conteo consolidado en UNIDADES (solo terminados idestado=1)
        // =============================
        $qConteo = (new Query())
            ->from(['gsc' => 'grumascanconteo'])
            ->innerJoin(['m' => 'grumascanmarcacion'], 'm.id = gsc.idmarcacion')
            ->innerJoin(['b' => 'bodegas'], 'b.id = m.idbodega')
            ->innerJoin(['gscd' => 'grumascanconteodetalle'], 'gscd.idgrumascanconteo = gsc.id')
            ->innerJoin(['it' => 'item'], 'it.id = gscd.idItem')
            ->leftJoin(['ue' => 'unidadEmpaque'], 'ue.codigo = it.unidadEmpaque')
            ->where(['gsc.idestado' => 1])
            ->select([
                // ✅ tienda normalizada
                'codigoBodega' => $normBodega('b.codigo'),
                'item' => 'it.item',

                // ✅ idColor/idTalla como TEXTO (sin convertir a int)
                'idColor' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idColor AS NVARCHAR(50)))), '')"),
                'idTalla' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idTalla AS NVARCHAR(50)))), '')"),

                // ✅ conteo en unidades
                'conteo_unidades' => new Expression("
                    SUM(
                        CASE
                            WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                            ELSE gscd.cantidad
                        END
                    )
                "),
            ])
            ->groupBy([
                $normBodega('b.codigo'),
                'it.item',
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idColor AS NVARCHAR(50)))), '')"),
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idTalla AS NVARCHAR(50)))), '')"),
            ]);

        // =============================
        // 2) Inventario en UNIDADES (ya está en unidades)
        //    Tomar 1 solo registro por SKU: MAX(existencia)
        // =============================
        $qInv = (new Query())
            ->from(['inv' => 'inventario'])
            ->innerJoin(['it2' => 'item'], 'it2.id = inv.idItem')
            ->select([
                // ✅ tienda normalizada
                'codigoBodega' => $normBodega('inv.codigoBodega'),
                'item' => 'it2.item',

                // ✅ texto
                'idColor' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                'idTalla' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),

                'existencia_unidades' => new Expression('MAX(inv.existencia)'),
            ])
            ->groupBy([
                $normBodega('inv.codigoBodega'),
                'it2.item',
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),
            ]);

        // =============================
        // 3) FULL OUTER JOIN simulado (A union B)
        // =============================
        $joinOn = "
            i.codigoBodega = c.codigoBodega
            AND i.item = c.item
            AND ISNULL(i.idColor, '') = ISNULL(c.idColor, '')
            AND ISNULL(i.idTalla, '') = ISNULL(c.idTalla, '')
        ";

        $qA = (new Query())
            ->from(['c' => $qConteo])
            ->leftJoin(['i' => $qInv], $joinOn)
            ->select([
                'codigoBodega' => 'c.codigoBodega',
                'item' => 'c.item',
                'idColor' => 'c.idColor',
                'idTalla' => 'c.idTalla',
                'conteo_unidades' => new Expression('ISNULL(c.conteo_unidades, 0)'),
                'existencia_unidades' => new Expression('ISNULL(i.existencia_unidades, 0)'),
                'diferencia' => new Expression('ISNULL(c.conteo_unidades, 0) - ISNULL(i.existencia_unidades, 0)'),
            ]);

        $qB = (new Query())
            ->from(['i' => $qInv])
            ->leftJoin(['c' => $qConteo], str_replace(['i.', 'c.'], ['c.', 'i.'], $joinOn))
            ->where(['c.codigoBodega' => null]) // solo los que no existen en conteo
            ->select([
                'codigoBodega' => 'i.codigoBodega',
                'item' => 'i.item',
                'idColor' => 'i.idColor',
                'idTalla' => 'i.idTalla',
                'conteo_unidades' => new Expression('ISNULL(c.conteo_unidades, 0)'),
                'existencia_unidades' => new Expression('ISNULL(i.existencia_unidades, 0)'),
                'diferencia' => new Expression('ISNULL(c.conteo_unidades, 0) - ISNULL(i.existencia_unidades, 0)'),
            ]);

        $qUnion = (new Query())->from(['u' => $qA->union($qB, true)]);

        // =============================
        // 4) Nombres (tienda / color / talla)
        // - JOIN a color/talla con TRY_CONVERT
        // =============================
        $qFinal = (new Query())
            ->from(['u' => $qUnion])
            ->leftJoin(['b' => 'bodegas'], 'b.codigo = u.codigoBodega')
            ->leftJoin(['c' => 'color'], 'c.id = TRY_CONVERT(int, u.idColor)')
            ->leftJoin(['t' => 'talla'], 't.id = TRY_CONVERT(int, u.idTalla)')
            ->select([
                'codigoBodega' => 'u.codigoBodega',
                'tienda' => new Expression('COALESCE(b.nombre, u.codigoBodega)'),

                'item' => 'u.item',
                'idColor' => 'u.idColor',
                'idTalla' => 'u.idTalla',

                'color' => new Expression("COALESCE(c.nombre, 'NA')"),
                'talla' => new Expression("COALESCE(t.nombre, 'NA')"),

                'conteo_unidades' => 'u.conteo_unidades',
                'existencia_unidades' => 'u.existencia_unidades',
                'diferencia' => 'u.diferencia',
            ]);

        // =============================
        // 5) Filtros
        // =============================
        if ($this->tienda !== null && trim($this->tienda) !== '') {
            $qFinal->andWhere(['u.codigoBodega' => trim($this->tienda)]);
        }

        if ($this->item !== null && trim($this->item) !== '') {
            $qFinal->andWhere(['like', 'u.item', trim($this->item)]);
        }

        if ($this->color !== null && trim($this->color) !== '') {
            $qFinal->andWhere(['like', new Expression("COALESCE(c.nombre,'NA')"), trim($this->color)]);
        }

        if ($this->talla !== null && trim($this->talla) !== '') {
            $qFinal->andWhere(['like', new Expression("COALESCE(t.nombre,'NA')"), trim($this->talla)]);
        }

        // =============================
        // 6) DataProvider
        // =============================
        $totalCount = (int)(new Query())->from(['x' => $qFinal])->count('*', Yii::$app->db);

        $sql = $qFinal->createCommand(Yii::$app->db)->getRawSql();

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'codigoBodega',
                    'tienda',
                    'item',
                    'color',
                    'talla',
                    'conteo_unidades',
                    'existencia_unidades',
                    'diferencia',
                ],
                'defaultOrder' => [
                    'codigoBodega' => SORT_ASC,
                    'item' => SORT_ASC,
                ],
            ],
        ]);

        // =============================
        // 7) Totales por tienda arriba (INVENTARIO TOTAL vs CONTEO TOTAL)
        // - Inventario total: suma de existencias por SKU en la tienda (MAX(existencia) por SKU)
        // - Conteo total: suma de conteo_unidades (terminados idestado=1)
        // - FULL OUTER JOIN por tienda (simulado con union A/B)
        // =============================

        // 7.1) Conteo total por tienda (terminados)
        $qConteoTienda = (new Query())
            ->from(['gsc' => 'grumascanconteo'])
            ->innerJoin(['m' => 'grumascanmarcacion'], 'm.id = gsc.idmarcacion')
            ->innerJoin(['b' => 'bodegas'], 'b.id = m.idbodega')
            ->innerJoin(['gscd' => 'grumascanconteodetalle'], 'gscd.idgrumascanconteo = gsc.id')
            ->innerJoin(['it' => 'item'], 'it.id = gscd.idItem')
            ->leftJoin(['ue' => 'unidadEmpaque'], 'ue.codigo = it.unidadEmpaque')
            ->where(['gsc.idestado' => 1])
            ->select([
                'codigoBodega' => $normBodega('b.codigo'),
                'total_conteo' => new Expression("
            SUM(
                CASE
                    WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                    ELSE gscd.cantidad
                END
            )
        "),
            ])
            ->groupBy([$normBodega('b.codigo')]);

        // 7.2) Inventario total por tienda
        // OJO: primero consolidamos a nivel SKU (MAX existencia), luego sumamos por tienda.
        $qInvSku = (new Query())
            ->from(['inv' => 'inventario'])
            ->innerJoin(['it2' => 'item'], 'it2.id = inv.idItem')
            ->select([
                'codigoBodega' => $normBodega('inv.codigoBodega'),
                'item' => 'it2.item',
                'idColor' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                'idTalla' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),
                'existencia_sku' => new Expression('MAX(inv.existencia)'),
            ])
            ->groupBy([
                $normBodega('inv.codigoBodega'),
                'it2.item',
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),
            ]);

        $qInvTienda = (new Query())
            ->from(['x' => $qInvSku])
            ->select([
                'codigoBodega' => 'x.codigoBodega',
                'total_existencia' => new Expression('SUM(x.existencia_sku)'),
            ])
            ->groupBy(['x.codigoBodega']);

        // 7.3) FULL OUTER JOIN (simulado) por tienda
        $joinTienda = "i.codigoBodega = c.codigoBodega";

        $qTA = (new Query())
            ->from(['c' => $qConteoTienda])
            ->leftJoin(['i' => $qInvTienda], $joinTienda)
            ->select([
                'codigoBodega' => 'c.codigoBodega',
                'total_conteo' => new Expression('ISNULL(c.total_conteo, 0)'),
                'total_existencia' => new Expression('ISNULL(i.total_existencia, 0)'),
                'total_diferencia' => new Expression('ISNULL(c.total_conteo, 0) - ISNULL(i.total_existencia, 0)'),
            ]);

        $qTB = (new Query())
            ->from(['i' => $qInvTienda])
            ->leftJoin(['c' => $qConteoTienda], $joinTienda)
            ->where(['c.codigoBodega' => null])
            ->select([
                'codigoBodega' => 'i.codigoBodega',
                'total_conteo' => new Expression('ISNULL(c.total_conteo, 0)'),
                'total_existencia' => new Expression('ISNULL(i.total_existencia, 0)'),
                'total_diferencia' => new Expression('ISNULL(c.total_conteo, 0) - ISNULL(i.total_existencia, 0)'),
            ]);

        $qTotalesUnion = (new Query())->from(['z' => $qTA->union($qTB, true)]);

        // 7.4) Agregamos nombre tienda (si existe en bodegas)
        $resumenTiendas = (new Query())
            ->from(['z' => $qTotalesUnion])
            ->leftJoin(['b' => 'bodegas'], 'b.codigo = z.codigoBodega')
            ->select([
                'codigoBodega' => 'z.codigoBodega',
                'tienda' => new Expression('COALESCE(b.nombre, z.codigoBodega)'),
                'total_conteo' => 'z.total_conteo',
                'total_existencia' => 'z.total_existencia',
                'total_diferencia' => 'z.total_diferencia',
            ])
            ->orderBy(['z.codigoBodega' => SORT_ASC])
            ->all(Yii::$app->db);


        return [
            'dataProvider' => $dataProvider,
            'resumenTiendas' => $resumenTiendas,
        ];
    }
}
