<?php

namespace common\components;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Transaction;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;

class TraspasoDetalleAuditadoService extends BaseObject
{
    /**
     * Mueve a *_delete (con el ejecutor) y borra los TDA indicados.
     * @param int[] $ids
     * @param int   $ejecutorUserId  Usuario que hace la eliminación (para historial)
     * @return array [insertados, borrados]
     * @throws \Throwable
     */
    public function moveToDeleteAndRemove(array $ids, int $ejecutorUserId): array
    {
        if (empty($ids)) return [0, 0];

        $db  = Yii::$app->db;
        $now = new Expression('GETDATE()');

        return $db->transaction(function (Transaction $tx) use ($db, $ids, $ejecutorUserId, $now) {
            $rows = (new Query())
                ->from('traspasodetalleauditado')
                ->where(['id' => $ids])
                ->all($db);

            if (!$rows) return [0, 0];

            $insertRows = [];
            foreach ($rows as $r) {
                $insertRows[] = [
                    'idTraspaso' => $r['idTraspaso'],
                    'idItem'     => $r['idItem'],
                    'cantidad'   => $r['cantidad'],
                    'created_at' => $now,
                    'created_by' => $ejecutorUserId,  // <- quién ejecuta el borrado
                    'updated_at' => $now,
                    'updated_by' => $ejecutorUserId,
                ];
            }

            $cols = ['idTraspaso', 'idItem', 'cantidad', 'created_at', 'created_by', 'updated_at', 'updated_by'];
            $db->createCommand()->batchInsert('traspasodetalleauditadodelete', $cols, $insertRows)->execute();
            $ins = count($insertRows);

            $del = $db->createCommand()->delete('traspasodetalleauditado', ['id' => $ids])->execute();

            return [$ins, $del];
        });
    }

    /**
     * Mueve y borra todos los TDA de un traspaso HECHOS por un usuario creador específico.
     * @return array [insertados, borrados]
     */
    public function purgeByTraspasoAndCreador(int $idTraspaso, int $creadorUserId, int $ejecutorUserId): array
    {
        $ids = (new Query())
            ->from('traspasodetalleauditado')
            ->where(['idTraspaso' => $idTraspaso, 'created_by' => $creadorUserId])
            ->select('id')
            ->column();

        return $this->moveToDeleteAndRemove($ids, $ejecutorUserId);
    }


    public function searchAgrupadoPorUsuario(array $params, ?int $idtraspaso): ActiveDataProvider
    {
        $q = (new Query())
            ->from('traspasodetalleauditado tda')
            ->select([
                'tda.idTraspaso',
                'tda.created_by',
                'total_registros' => new Expression('COUNT(*)'),
                'total_unidades'  => new Expression('SUM(tda.cantidad)'),
            ])
            ->groupBy(['tda.idTraspaso', 'tda.created_by']);

        if ($idtraspaso !== null) {
            $q->andWhere(['tda.idTraspaso' => (int)$idtraspaso]);
        }

        // Aquí puedes leer filtros desde $params si lo necesitas

        return new ActiveDataProvider([
            'query' => $q,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['idTraspaso' => SORT_DESC],
                'attributes' => [
                    'idTraspaso',
                    'created_by',
                    'total_registros',
                    'total_unidades',
                ],
            ],
        ]);
    }


    /**
     * Copia a traspasodetalleauditadodelete SOLO las filas con cantidad=0
     * de un traspaso + usuario creador. NO borra los originales.
     *
     * @return int número de filas insertadas en *_delete
     */
    public function archiveZerosByTraspasoAndCreador(
        int $idTraspaso,
        int $creadorUserId,
        int $ejecutorUserId
    ): int {
        $db = Yii::$app->db;

        $sql = "
        INSERT INTO dbo.traspasodetalleauditadodelete (
            idTraspaso, idItem, cantidad, created_at, created_by, updated_at, updated_by
        )
        SELECT t.idTraspaso, t.idItem, t.cantidad, GETDATE(), :exec1, GETDATE(), :exec2
        FROM dbo.traspasodetalleauditado AS t
        WHERE t.idTraspaso = :idTraspaso
          AND t.created_by = :creador
          AND t.cantidad = 0
          AND NOT EXISTS (
              SELECT 1
              FROM dbo.traspasodetalleauditadodelete AS d
              WHERE d.idTraspaso = t.idTraspaso
                AND d.idItem     = t.idItem
                AND d.cantidad   = t.cantidad
          )
    ";

        return (int)$db->createCommand($sql, [
            ':exec1'      => $ejecutorUserId,  // <- placeholders distintos
            ':exec2'      => $ejecutorUserId,  // <-
            ':idTraspaso' => $idTraspaso,
            ':creador'    => $creadorUserId,
        ])->execute();
    }



    /** (Opcional) versión por TODO el traspaso (todos los usuarios) */
    public function archiveZerosByTraspaso(int $idTraspaso, int $ejecutorUserId): int
    {
        $db  = Yii::$app->db;
        $now = new Expression('GETDATE()');

        $sql = "
        INSERT INTO traspasodetalleauditadodelete (
            idTraspaso, idItem, cantidad, created_at, created_by, updated_at, updated_by
        )
        SELECT t.idTraspaso, t.idItem, t.cantidad, :now, :exec, :now, :exec
        FROM traspasodetalleauditado t
        WHERE t.idTraspaso = :idTraspaso
          AND t.cantidad = 0
          AND NOT EXISTS (
              SELECT 1
              FROM traspasodetalleauditadodelete d
              WHERE d.idTraspaso = t.idTraspaso
                AND d.idItem     = t.idItem
                AND d.cantidad   = t.cantidad
          )
    ";

        return (int)$db->createCommand($sql, [
            ':now'        => $now,
            ':exec'       => $ejecutorUserId,
            ':idTraspaso' => $idTraspaso,
        ])->execute();
    }

    /**
     * Archiva y BORRA de traspasodetalleauditado SOLO las filas con cantidad = 0
     * de (idTraspaso + created_by). Retorna [insertados_en_historial, borrados].
     */
    public function deleteZerosByTraspasoAndCreador(
        int $idTraspaso,
        int $creadorUserId,
        int $ejecutorUserId
    ): array {
        $db = \Yii::$app->db;

        return $db->transaction(function () use ($db, $idTraspaso, $creadorUserId, $ejecutorUserId) {
            // 1) Archivar (por seguridad, con NOT EXISTS para no duplicar)
            $sqlArchive = "
            INSERT INTO dbo.traspasodetalleauditadodelete (
                idTraspaso, idItem, cantidad, created_at, created_by, updated_at, updated_by
            )
            SELECT t.idTraspaso, t.idItem, t.cantidad, GETDATE(), :exec1, GETDATE(), :exec2
            FROM dbo.traspasodetalleauditado AS t
            WHERE t.idTraspaso = :idTraspaso
              AND t.created_by = :creador
              AND t.cantidad = 0
              AND NOT EXISTS (
                  SELECT 1
                  FROM dbo.traspasodetalleauditadodelete AS d
                  WHERE d.idTraspaso = t.idTraspaso
                    AND d.idItem     = t.idItem
                    AND d.cantidad   = t.cantidad
              )
        ";
            $inserted = (int)$db->createCommand($sqlArchive, [
                ':exec1'      => $ejecutorUserId,   // placeholders distintos (ODBC safe)
                ':exec2'      => $ejecutorUserId,
                ':idTraspaso' => $idTraspaso,
                ':creador'    => $creadorUserId,
            ])->execute();

            // 2) Borrar los ceros del traspaso + usuario
            $deleted = (int)$db->createCommand("
            DELETE FROM dbo.traspasodetalleauditado
            WHERE idTraspaso = :idTraspaso
              AND created_by = :creador
              AND cantidad   = 0
        ", [
                ':idTraspaso' => $idTraspaso,
                ':creador'    => $creadorUserId,
            ])->execute();

            return [$inserted, $deleted];
        });
    }
}
