<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Snapshot de inventario Siesa capturado al momento de crear un grumascanconteo.
 *
 * @property int      $id
 * @property int      $idgrumascanconteo
 * @property int|null $idItem
 * @property string   $codigoBarras
 * @property string   $codigoBodega
 * @property float    $existencia
 * @property string   $fecha_snapshot
 */
class GrumascanInventarioSnapshot extends ActiveRecord
{
    public static function tableName()
    {
        return 'grumascan_inventario_snapshot';
    }

    public function rules()
    {
        return [
            [['idgrumascanconteo', 'codigoBarras', 'codigoBodega'], 'required'],
            [['idgrumascanconteo', 'idItem'], 'integer'],
            [['existencia'], 'number'],
            [['fecha_snapshot'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['codigoBodega'], 'string', 'max' => 20],
        ];
    }

    public function getConteo()
    {
        return $this->hasOne(Grumascanconteo::class, ['id' => 'idgrumascanconteo']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Crea (o regenera) el snapshot de inventario Siesa para el conteo indicado.
     *
     * 1. Consulta t400_cm_existencia en Siesa para la bodega.
     * 2. Mapea codigoBarras → idItem usando la tabla local `inventario`.
     * 3. Elimina filas previas para este conteo (idempotente).
     * 4. Inserta en lotes de 200 filas.
     *
     * @param int    $idConteo      id de grumascanconteo
     * @param string $codigoBodega  código de bodega (ej. "090")
     * @return array{rows:int, errors:string[]}
     */
    public static function crearDesdeConteo(int $idConteo, string $codigoBodega): array
    {
        $errors      = [];
        $rowsInserted = 0;

        // ── 1. Existencias desde Siesa ─────────────────────────────────────────
        $sqlSiesa = "
            SELECT
                LTRIM(RTRIM(t131.f131_id_codigo_barras))  AS codigoBarras,
                t400.f400_cant_existencia_1               AS existencia
            FROM  t400_cm_existencia    t400
            JOIN  t150_mc_bodegas       t150
              ON  t400.f400_rowid_bodega    = t150.f150_rowid
            LEFT JOIN t131_mc_items_barras t131
              ON  t400.f400_rowid_item_ext  = t131.f131_rowid_item_ext
            WHERE t150.f150_id = :codigoBodega
              AND t131.f131_id_codigo_barras IS NOT NULL
              AND LTRIM(RTRIM(t131.f131_id_codigo_barras)) <> ''
        ";

        try {
            $siesaRows = Yii::$app->dbSiesa
                ->createCommand($sqlSiesa, [':codigoBodega' => $codigoBodega])
                ->queryAll();
        } catch (\Exception $e) {
            return ['rows' => 0, 'errors' => ['Error consultando Siesa: ' . $e->getMessage()]];
        }

        if (empty($siesaRows)) {
            return ['rows' => 0, 'errors' => ['Sin existencias Siesa para bodega: ' . $codigoBodega]];
        }

        // ── 2. Mapa codigoBarras → idItem desde inventario local ──────────────
        $allBarcodes = array_unique(array_column($siesaRows, 'codigoBarras'));
        $barcodeMap  = []; // codigoBarras => idItem

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

        // ── 3. Eliminar snapshot previo para este conteo (idempotente) ─────────
        Yii::$app->db->createCommand()
            ->delete('grumascan_inventario_snapshot', ['idgrumascanconteo' => $idConteo])
            ->execute();

        // ── 4. Insertar en lotes de 200 ────────────────────────────────────────
        $batch        = [];
        $fechaSnap    = date('Y-m-d H:i:s');
        $columns      = ['idgrumascanconteo', 'idItem', 'codigoBarras', 'codigoBodega', 'existencia', 'fecha_snapshot'];

        foreach ($siesaRows as $row) {
            $barras     = trim((string)$row['codigoBarras']);
            $existencia = (float)($row['existencia'] ?? 0);
            $idItem     = $barcodeMap[$barras] ?? null;

            $batch[] = [$idConteo, $idItem, $barras, $codigoBodega, $existencia, $fechaSnap];

            if (count($batch) >= 200) {
                Yii::$app->db->createCommand()
                    ->batchInsert('grumascan_inventario_snapshot', $columns, $batch)
                    ->execute();
                $rowsInserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Yii::$app->db->createCommand()
                ->batchInsert('grumascan_inventario_snapshot', $columns, $batch)
                ->execute();
            $rowsInserted += count($batch);
        }

        return ['rows' => $rowsInserted, 'errors' => $errors];
    }
}
