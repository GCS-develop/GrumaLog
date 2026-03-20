<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Snapshot maestro de inventario Siesa para una bodega en un momento dado.
 * Puede asignarse a múltiples grumascanconteo para que el consolidado
 * compare contra este inventario congelado en lugar del inventario actual.
 *
 * @property int         $id
 * @property int|null    $idbodega
 * @property string      $codigoBodega
 * @property string|null $descripcion
 * @property int         $total_items
 * @property string      $fecha_snapshot
 * @property int|null    $created_by
 * @property string      $created_at
 */
class GrumascanSnapshot extends ActiveRecord
{
    public static function tableName()
    {
        return 'grumascan_snapshot';
    }

    public function rules()
    {
        return [
            [['codigoBodega'], 'required'],
            [['idbodega', 'total_items', 'created_by'], 'integer'],
            [['codigoBodega'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 200],
            [['fecha_snapshot', 'created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'idbodega'       => 'Bodega',
            'codigoBodega'   => 'Código Bodega',
            'descripcion'    => 'Descripción',
            'total_items'    => 'Total Items',
            'fecha_snapshot' => 'Fecha Snapshot',
            'created_by'     => 'Creado por',
            'created_at'     => 'Creado en',
        ];
    }

    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idbodega']);
    }

    public function getDetalles()
    {
        return $this->hasMany(GrumascanSnapshotDetalle::class, ['idSnapshot' => 'id']);
    }

    public function getConteos()
    {
        return $this->hasMany(Grumascanconteo::class, ['idSnapshot' => 'id']);
    }

    /**
     * Captura el inventario de Siesa para la bodega indicada y lo guarda como snapshot.
     * Inserta en grumascan_snapshot (ya guardado) y en grumascan_snapshot_detalle en lotes.
     *
     * @return array{rows:int, errors:string[]}
     */
    public function capturarDesdeSiesa(): array
    {
        $errors       = [];
        $rowsInserted = 0;
        $codigoBodega = $this->codigoBodega;

        // ── 1. Existencias desde Siesa ─────────────────────────────────────────
        $sqlSiesa = "
            SELECT
                LTRIM(RTRIM(t131.f131_id_codigo_barras)) AS codigoBarras,
                t400.f400_cant_existencia_1              AS existencia
            FROM  t400_cm_existencia     t400
            JOIN  t150_mc_bodegas        t150
              ON  t400.f400_rowid_bodega    = t150.f150_rowid
            LEFT JOIN t131_mc_items_barras t131
              ON  t400.f400_rowid_item_ext  = t131.f131_rowid_item_ext
            WHERE t150.f150_id = :cod
              AND t131.f131_id_codigo_barras IS NOT NULL
              AND LTRIM(RTRIM(t131.f131_id_codigo_barras)) <> ''
        ";

        try {
            $siesaRows = Yii::$app->dbSiesa
                ->createCommand($sqlSiesa, [':cod' => $codigoBodega])
                ->queryAll();
        } catch (\Exception $e) {
            return ['rows' => 0, 'errors' => ['Error consultando Siesa: ' . $e->getMessage()]];
        }

        if (empty($siesaRows)) {
            return ['rows' => 0, 'errors' => ['Sin existencias Siesa para bodega: ' . $codigoBodega]];
        }

        // ── 2. Mapa codigoBarras → idItem desde inventario local ──────────────
        $allBarcodes = array_unique(array_column($siesaRows, 'codigoBarras'));
        $barcodeMap  = [];

        foreach (array_chunk($allBarcodes, 200) as $chunk) {
            $rows = (new Query())
                ->from('inventario')
                ->select(['codigoBarras', 'idItem'])
                ->where(['codigoBodega' => $codigoBodega, 'codigoBarras' => $chunk])
                ->all(Yii::$app->db);
            foreach ($rows as $r) {
                $barcodeMap[$r['codigoBarras']] = (int)$r['idItem'];
            }
        }

        // ── 3. Insertar detalles en lotes de 200 ──────────────────────────────
        $batch   = [];
        $columns = ['idSnapshot', 'codigoBarras', 'idItem', 'existencia'];

        foreach ($siesaRows as $row) {
            $barras     = trim((string)$row['codigoBarras']);
            $existencia = (float)($row['existencia'] ?? 0);
            $idItem     = $barcodeMap[$barras] ?? null;

            $batch[] = [$this->id, $barras, $idItem, $existencia];

            if (count($batch) >= 200) {
                Yii::$app->db->createCommand()
                    ->batchInsert('grumascan_snapshot_detalle', $columns, $batch)
                    ->execute();
                $rowsInserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Yii::$app->db->createCommand()
                ->batchInsert('grumascan_snapshot_detalle', $columns, $batch)
                ->execute();
            $rowsInserted += count($batch);
        }

        // ── 4. Actualizar total_items en la cabecera ───────────────────────────
        $this->total_items = $rowsInserted;
        $this->save(false);

        return ['rows' => $rowsInserted, 'errors' => $errors];
    }

    /**
     * Asigna este snapshot a todos los conteos de la bodega en el rango de fechas dado.
     * Solo afecta conteos en estado=1 (terminado) sin snapshot asignado previamente
     * (a menos que $sobrescribir sea true).
     *
     * @return int filas afectadas
     */
    public function asignarConteosPorFecha(string $desde, string $hasta, bool $sobrescribir = false): int
    {
        // Conteos de esta bodega en el rango
        $subQuery = (new Query())
            ->select(['gsc.id'])
            ->from(['gsc' => 'grumascanconteo'])
            ->innerJoin(['m' => 'grumascanmarcacion'], 'm.id = gsc.idmarcacion')
            ->innerJoin(['b' => 'bodegas'], 'b.id = m.idbodega')
            ->where([
                'gsc.idestado' => 1,
                new \yii\db\Expression("
                    CASE
                        WHEN TRY_CONVERT(int, LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))) IS NOT NULL
                            THEN RIGHT('000' + LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50)))), 3)
                        ELSE LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))
                    END = '{$this->codigoBodega}'
                "),
            ])
            ->andWhere(['between',
                new \yii\db\Expression('CAST(gsc.created_at AS DATE)'),
                date('Y-m-d', strtotime($desde)),
                date('Y-m-d', strtotime($hasta))
            ]);

        if (!$sobrescribir) {
            $subQuery->andWhere(['gsc.idSnapshot' => null]);
        }

        $ids = $subQuery->column(Yii::$app->db);

        if (empty($ids)) {
            return 0;
        }

        return Yii::$app->db->createCommand()
            ->update('grumascanconteo', ['idSnapshot' => $this->id], ['id' => $ids])
            ->execute();
    }

    /**
     * Lista snapshots disponibles para una bodega, ordenados del más reciente.
     */
    public static function listaPorBodega(string $codigoBodega): array
    {
        return static::find()
            ->where(['codigoBodega' => $codigoBodega])
            ->orderBy(['fecha_snapshot' => SORT_DESC])
            ->all();
    }
}
