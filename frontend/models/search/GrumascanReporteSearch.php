<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;

class GrumascanReporteSearch extends Model
{
    public $tienda; // OBLIGATORIO
    public $item;
    public $color;
    public $talla;

    // Fecha por grumascanconteo.created_at
    public $created_from;
    public $created_to;

    // all | diff | nodiff
    public $dif_mode = 'all';
    public $min_abs_dif = 0;

    public function rules()
    {
        return [
            [['tienda', 'item', 'color', 'talla', 'dif_mode', 'created_from', 'created_to'], 'safe'],
            [['min_abs_dif'], 'number'],
        ];
    }

    public function searchConsolidadoConteoVsInventario($params)
    {
        $this->load($params);

        $tienda = trim((string)$this->tienda);
        if ($tienda === '') {
            return [
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => [],
                    'pagination' => ['pageSize' => 50],
                ]),
                'resumenTiendas' => [],
            ];
        }

        if (ctype_digit($tienda)) {
            $tienda = str_pad($tienda, 3, '0', STR_PAD_LEFT);
        }

        $difMode = in_array($this->dif_mode, ['all', 'diff', 'nodiff'], true) ? $this->dif_mode : 'all';
        $minAbs  = (float)($this->min_abs_dif ?? 0);

        // ✅ default fechaDesde como tu ejemplo (opcional)
        // si no quieres default, borra estas 3 líneas
        if (empty($this->created_from) && empty($this->created_to)) {
            $this->created_from = date('Y-m-01');
        }

        $normBodega = function (string $sqlExpr) {
            return new Expression("
                CASE
                    WHEN TRY_CONVERT(int, LTRIM(RTRIM($sqlExpr))) IS NOT NULL
                        THEN RIGHT('000' + LTRIM(RTRIM($sqlExpr)), 3)
                    ELSE LTRIM(RTRIM($sqlExpr))
                END
            ");
        };

        // ✅ ARREGLADO: aquí estaba el paréntesis faltante (causaba el 07002 en COUNT)
        $normBodegaFromBodegasCodigo = function (string $sqlExpr) {
            return new Expression("
                CASE
                    WHEN TRY_CONVERT(int, LTRIM(RTRIM(CAST($sqlExpr AS NVARCHAR(50))))) IS NOT NULL
                        THEN RIGHT('000' + LTRIM(RTRIM(CAST($sqlExpr AS NVARCHAR(50)))), 3)
                    ELSE LTRIM(RTRIM(CAST($sqlExpr AS NVARCHAR(50))))
                END
            ");
        };

        $itemAsText_it  = new Expression("LTRIM(RTRIM(CAST(it.item  AS NVARCHAR(50))))");
        $itemAsText_it2 = new Expression("LTRIM(RTRIM(CAST(it2.item AS NVARCHAR(50))))");

        // ============================================================
        // 1) Conteo consolidado (estado=1)
        //    ✅ filtro por fecha usando CAST(gsc.created_at AS DATE)
        // ============================================================
        $qConteo = (new Query())
            ->from(['gsc' => 'grumascanconteo'])
            ->innerJoin(['m' => 'grumascanmarcacion'], 'm.id = gsc.idmarcacion')
            ->innerJoin(['b' => 'bodegas'], 'b.id = m.idbodega')
            ->innerJoin(['gscd' => 'grumascanconteodetalle'], 'gscd.idgrumascanconteo = gsc.id')
            ->innerJoin(['it' => 'item'], 'it.id = gscd.idItem')
            ->leftJoin(['ue' => 'unidadEmpaque'], 'ue.codigo = it.unidadEmpaque')
            ->where(['gsc.idestado' => 1])
            ->select([
                'codigoBodega' => $normBodega('b.codigo'),
                'item' => $itemAsText_it,
                'item_descripcion' => new Expression("MAX(LTRIM(RTRIM(CAST(it.descripcion AS NVARCHAR(255)))))"),
                'idColor' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idColor AS NVARCHAR(50)))), '')"),
                'idTalla' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idTalla AS NVARCHAR(50)))), '')"),
                'conteo_unidades' => new Expression("
                    CAST(
                        SUM(
                            CASE
                                WHEN TRY_CONVERT(DECIMAL(18,2), ue.equivalencia) IS NOT NULL
                                    THEN COALESCE(TRY_CONVERT(DECIMAL(18,2), gscd.cantidad), 0) * TRY_CONVERT(DECIMAL(18,2), ue.equivalencia)
                                ELSE COALESCE(TRY_CONVERT(DECIMAL(18,2), gscd.cantidad), 0)
                            END
                        ) AS DECIMAL(18,2)
                    )
                "),
            ])
            ->groupBy([
                $normBodega('b.codigo'),
                $itemAsText_it,
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idColor AS NVARCHAR(50)))), '')"),
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it.idTalla AS NVARCHAR(50)))), '')"),
            ]);

        // ✅ filtro fechas como tu patrón
        $fechaDesde = trim((string)$this->created_from);
        $fechaHasta = trim((string)$this->created_to);

        if ($fechaDesde || $fechaHasta) {
            if ($fechaDesde && !$fechaHasta) {
                $f = date('Y-m-d', strtotime($fechaDesde));
                $qConteo->andWhere(['>=', new Expression('CAST(gsc.created_at AS DATE)'), $f]);
            } elseif (!$fechaDesde && $fechaHasta) {
                $f = date('Y-m-d', strtotime($fechaHasta));
                $qConteo->andWhere(['<=', new Expression('CAST(gsc.created_at AS DATE)'), $f]);
            } else {
                $f1 = date('Y-m-d', strtotime($fechaDesde));
                $f2 = date('Y-m-d', strtotime($fechaHasta));
                $qConteo->andWhere(['between', new Expression('CAST(gsc.created_at AS DATE)'), $f1, $f2]);
            }
        }

        // ============================================================
        // 2) Inventario por SKU lógico
        // ============================================================
        $qInv = (new Query())
            ->from(['inv' => 'inventario'])
            ->innerJoin(['it2' => 'item'], 'it2.id = inv.idItem')
            ->select([
                'codigoBodega' => $normBodega('inv.codigoBodega'),
                'item' => $itemAsText_it2,
                'item_descripcion' => new Expression("MAX(LTRIM(RTRIM(CAST(it2.descripcion AS NVARCHAR(255)))))"),
                'idColor' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                'idTalla' => new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),
                'existencia_unidades' => new Expression("
                    CAST(
                        MAX(COALESCE(TRY_CONVERT(DECIMAL(18,2), inv.existencia), 0))
                        AS DECIMAL(18,2)
                    )
                "),
            ])
            ->groupBy([
                $normBodega('inv.codigoBodega'),
                $itemAsText_it2,
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idColor AS NVARCHAR(50)))), '')"),
                new Expression("NULLIF(LTRIM(RTRIM(CAST(it2.idTalla AS NVARCHAR(50)))), '')"),
            ]);

        // ============================================================
        // 3) FULL OUTER JOIN simulado (A ∪ B ∪ huérfanos)
        // ============================================================
        $joinOn_C_left_I = "
            i.codigoBodega = c.codigoBodega
            AND i.item = c.item
            AND ISNULL(i.idColor, '') = ISNULL(c.idColor, '')
            AND ISNULL(i.idTalla, '') = ISNULL(c.idTalla, '')
        ";

        $joinOn_I_left_C = "
            c.codigoBodega = i.codigoBodega
            AND c.item = i.item
            AND ISNULL(c.idColor, '') = ISNULL(i.idColor, '')
            AND ISNULL(c.idTalla, '') = ISNULL(i.idTalla, '')
        ";

        $qA = (new Query())
            ->from(['c' => $qConteo])
            ->leftJoin(['i' => $qInv], $joinOn_C_left_I)
            ->select([
                'codigoBodega' => 'c.codigoBodega',
                'item' => 'c.item',
                'item_descripcion' => new Expression("COALESCE(NULLIF(c.item_descripcion,''), i.item_descripcion)"),
                'idColor' => 'c.idColor',
                'idTalla' => 'c.idTalla',
                'conteo_unidades' => new Expression("CAST(ISNULL(c.conteo_unidades, 0) AS DECIMAL(18,2))"),
                'existencia_unidades' => new Expression("CAST(ISNULL(i.existencia_unidades, 0) AS DECIMAL(18,2))"),
                'diferencia' => new Expression("CAST(ISNULL(c.conteo_unidades, 0) - ISNULL(i.existencia_unidades, 0) AS DECIMAL(18,2))"),
            ]);

        $qB = (new Query())
            ->from(['i' => $qInv])
            ->leftJoin(['c' => $qConteo], $joinOn_I_left_C)
            ->where(['c.codigoBodega' => null])
            ->select([
                'codigoBodega' => 'i.codigoBodega',
                'item' => 'i.item',
                'item_descripcion' => 'i.item_descripcion',
                'idColor' => 'i.idColor',
                'idTalla' => 'i.idTalla',
                'conteo_unidades' => new Expression("CAST(0 AS DECIMAL(18,2))"),
                'existencia_unidades' => new Expression("CAST(ISNULL(i.existencia_unidades, 0) AS DECIMAL(18,2))"),
                'diferencia' => new Expression("CAST(0 - ISNULL(i.existencia_unidades, 0) AS DECIMAL(18,2))"),
            ]);

        $qHuerfanos = (new Query())
            ->from(['inv' => 'inventario'])
            ->leftJoin(['itx' => 'item'], 'itx.id = inv.idItem')
            ->where(['itx.id' => null])
            ->select([
                'codigoBodega' => $normBodega('inv.codigoBodega'),
                'item' => new Expression("CAST('__SIN_ITEM__' AS NVARCHAR(50))"),
                'item_descripcion' => new Expression("CAST('' AS NVARCHAR(255))"),
                'idColor' => new Expression("NULL"),
                'idTalla' => new Expression("NULL"),
                'conteo_unidades' => new Expression("CAST(0 AS DECIMAL(18,2))"),
                'existencia_unidades' => new Expression("CAST(SUM(COALESCE(TRY_CONVERT(DECIMAL(18,2), inv.existencia), 0)) AS DECIMAL(18,2))"),
                'diferencia' => new Expression("CAST(0 - SUM(COALESCE(TRY_CONVERT(DECIMAL(18,2), inv.existencia), 0)) AS DECIMAL(18,2))"),
            ])
            ->groupBy([$normBodega('inv.codigoBodega')]);

        $qUnion = (new Query())
            ->from(['u' => $qA->union($qB, true)->union($qHuerfanos, true)]);

        // ============================================================
        // 4) Final
        // ============================================================
        $bodegaJoin = $normBodegaFromBodegasCodigo('b.codigo')->expression . " = u.codigoBodega";

        $qFinalBase = (new Query())
            ->from(['u' => $qUnion])
            ->leftJoin(['b' => 'bodegas'], new Expression($bodegaJoin))
            ->leftJoin(['c' => 'color'], 'c.id = TRY_CONVERT(int, u.idColor)')
            ->leftJoin(['t' => 'talla'], 't.id = TRY_CONVERT(int, u.idTalla)')
            ->select([
                'codigoBodega' => 'u.codigoBodega',
                'tienda' => new Expression('COALESCE(b.nombre, u.codigoBodega)'),
                'item' => 'u.item',
                'item_descripcion' => 'u.item_descripcion',
                'idColor' => 'u.idColor',
                'idTalla' => 'u.idTalla',
                'color' => new Expression("COALESCE(c.nombre, 'NA')"),
                'talla' => new Expression("COALESCE(t.nombre, 'NA')"),
                'conteo_unidades' => 'u.conteo_unidades',
                'existencia_unidades' => 'u.existencia_unidades',
                'diferencia' => 'u.diferencia',
                'abs_diferencia' => new Expression('ABS(u.diferencia)'),
                'has_conteo' => new Expression("CASE WHEN u.conteo_unidades > 0 THEN 1 ELSE 0 END"),
                'is_huerfano' => new Expression("CASE WHEN u.item = '__SIN_ITEM__' THEN 1 ELSE 0 END"),
            ]);

        // ============================================================
        // 5) Filtros
        // ============================================================
        $qFinalBase->andWhere(['u.codigoBodega' => $tienda]);

        if ($this->item !== null && trim($this->item) !== '') {
            $qFinalBase->andWhere(['like', 'u.item', trim($this->item)]);
        }
        if ($this->color !== null && trim($this->color) !== '') {
            $qFinalBase->andWhere(['like', new Expression("COALESCE(c.nombre,'NA')"), trim($this->color)]);
        }
        if ($this->talla !== null && trim($this->talla) !== '') {
            $qFinalBase->andWhere(['like', new Expression("COALESCE(t.nombre,'NA')"), trim($this->talla)]);
        }

        if ($difMode === 'diff') {
            $qFinalBase->andWhere(new Expression('u.diferencia <> 0'));
        } elseif ($difMode === 'nodiff') {
            $qFinalBase->andWhere(new Expression('u.diferencia = 0'));
        }

        if ($minAbs > 0) {
            $qFinalBase->andWhere(new Expression('ABS(u.diferencia) >= :minAbs', [':minAbs' => $minAbs]));
        }

        // ============================================================
        // 6) DataProvider (COUNT arreglado por el paréntesis faltante)
        // ============================================================
        $qFinalDetalle = clone $qFinalBase;

        $totalCount = (int)(new Query())->from(['x' => $qFinalDetalle])->count('*', Yii::$app->db);
        $sqlDetalle = $qFinalDetalle->createCommand(Yii::$app->db)->getRawSql();

        $dataProvider = new SqlDataProvider([
            'sql' => $sqlDetalle,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'codigoBodega',
                    'tienda',
                    'item',
                    'item_descripcion',
                    'color',
                    'talla',
                    'conteo_unidades',
                    'existencia_unidades',
                    'diferencia',
                    'abs_diferencia',
                    'has_conteo',
                    'is_huerfano',
                ],
                'defaultOrder' => [
                    'has_conteo' => SORT_DESC,
                    'is_huerfano' => SORT_ASC,
                    'abs_diferencia' => SORT_DESC,
                    'diferencia' => SORT_DESC,
                    'item' => SORT_ASC,
                ],
            ],
        ]);

        // ============================================================
        // 7) Totales por tienda + %
        // ============================================================
        $qTotales = (new Query())
            ->from(['x' => $qFinalBase])
            ->select([
                'codigoBodega' => 'x.codigoBodega',
                'tienda' => new Expression('MAX(x.tienda)'),
                'total_conteo' => new Expression('CAST(SUM(x.conteo_unidades) AS DECIMAL(18,2))'),
                'total_existencia' => new Expression('CAST(SUM(x.existencia_unidades) AS DECIMAL(18,2))'),
                'total_diferencia' => new Expression('CAST(SUM(x.diferencia) AS DECIMAL(18,2))'),
                'pct_conteo_vs_inv' => new Expression("
                    CAST(
                        CASE WHEN SUM(x.existencia_unidades) = 0 THEN 0
                             ELSE (SUM(x.conteo_unidades) * 100.0) / NULLIF(SUM(x.existencia_unidades),0)
                        END
                    AS DECIMAL(18,2))
                "),
            ])
            ->groupBy(['x.codigoBodega'])
            ->all(Yii::$app->db);

        return [
            'dataProvider' => $dataProvider,
            'resumenTiendas' => $qTotales,
        ];
    }

    public function fetchMarcacionesDetalleHtml(array $payload): array
    {
        $codigoBodega = trim((string)($payload['codigoBodega'] ?? ''));
        $item         = trim((string)($payload['item'] ?? ''));
        $idColor      = trim((string)($payload['idColor'] ?? ''));
        $idTalla      = trim((string)($payload['idTalla'] ?? ''));

        $fechaDesde = trim((string)($payload['created_from'] ?? ''));
        $fechaHasta = trim((string)($payload['created_to'] ?? ''));

        if ($codigoBodega === '' || $item === '') {
            return [];
        }

        // Normaliza bodega a 3 dígitos si es numérica (090, 075, etc.)
        if (ctype_digit($codigoBodega)) {
            $codigoBodega = str_pad($codigoBodega, 3, '0', STR_PAD_LEFT);
        }

        $normBodegaExpr = new Expression("
        CASE
            WHEN TRY_CONVERT(int, LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))) IS NOT NULL
                THEN RIGHT('000' + LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50)))), 3)
            ELSE LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))
        END
    ");

        $q = (new Query())
            ->from(['gsc' => 'grumascanconteo'])
            ->innerJoin(['ge' => 'grumascanestado'], 'ge.id = gsc.idestado')
            ->innerJoin(['m' => 'grumascanmarcacion'], 'm.id = gsc.idmarcacion')
            ->innerJoin(['b' => 'bodegas'], 'b.id = m.idbodega')
            ->innerJoin(['gscd' => 'grumascanconteodetalle'], 'gscd.idgrumascanconteo = gsc.id')
            ->innerJoin(['it' => 'item'], 'it.id = gscd.idItem')

            // grumascanconteomanual: unidades_manual, unidades_sistema, diferencia
            ->leftJoin(['cm' => 'grumascanconteomanual'], 'cm.idgrumascanconteo = gsc.id')

            ->where(['gsc.idestado' => 1]) // terminados
            ->andWhere(new Expression($normBodegaExpr->expression . ' = :bod', [':bod' => $codigoBodega]))
            ->andWhere(new Expression("LTRIM(RTRIM(CAST(it.item AS NVARCHAR(50)))) = :item", [':item' => $item]))

            // ojo: idColor/idTalla pueden venir vacíos (NA). Si vienen vacíos, no filtro.
            ->select([
                'conteo_id' => 'gsc.id',
                'estado' => 'ge.nombre',
                'conteo_created_at' => new Expression("CONVERT(VARCHAR(19), gsc.created_at, 120)"),
                'marcacion_id' => 'm.id',
                'seccion' => new Expression("COALESCE(CAST(m.seccion AS NVARCHAR(100)), '')"),
                'ubicacion' => new Expression("COALESCE(CAST(m.ubicacion AS NVARCHAR(100)), '')"),
                'manual_unidades' => new Expression("
                CAST(SUM(COALESCE(TRY_CONVERT(DECIMAL(18,2), cm.unidades_manual), 0)) AS DECIMAL(18,2))
            "),
            ])
            ->groupBy([
                'gsc.id',
                'ge.nombre',
                new Expression("CONVERT(VARCHAR(19), gsc.created_at, 120)"),
                'm.id',
                new Expression("COALESCE(CAST(m.seccion AS NVARCHAR(100)), '')"),
                new Expression("COALESCE(CAST(m.ubicacion AS NVARCHAR(100)), '')"),
            ])
            ->orderBy([
                'm.id' => SORT_ASC,
                'gsc.id' => SORT_DESC,
            ])
            ->limit(2000);

        // Filtro por color/talla sólo si vienen
        if ($idColor !== '') {
            $q->andWhere(new Expression("
            ISNULL(NULLIF(LTRIM(RTRIM(CAST(it.idColor AS NVARCHAR(50)))), ''), '') = :col
        ", [':col' => $idColor]));
        }
        if ($idTalla !== '') {
            $q->andWhere(new Expression("
            ISNULL(NULLIF(LTRIM(RTRIM(CAST(it.idTalla AS NVARCHAR(50)))), ''), '') = :tal
        ", [':tal' => $idTalla]));
        }

        // ✅ filtro fecha como tu patrón (datetime en BD, filtramos por CAST a DATE)
        if ($fechaDesde || $fechaHasta) {
            if ($fechaDesde && !$fechaHasta) {
                $f = date('Y-m-d', strtotime($fechaDesde));
                $q->andWhere(['>=', new Expression('CAST(gsc.created_at AS DATE)'), $f]);
            } elseif (!$fechaDesde && $fechaHasta) {
                $f = date('Y-m-d', strtotime($fechaHasta));
                $q->andWhere(['<=', new Expression('CAST(gsc.created_at AS DATE)'), $f]);
            } else {
                $f1 = date('Y-m-d', strtotime($fechaDesde));
                $f2 = date('Y-m-d', strtotime($fechaHasta));
                $q->andWhere(['between', new Expression('CAST(gsc.created_at AS DATE)'), $f1, $f2]);
            }
        }

        return $q->all(Yii::$app->db);
    }
}
