<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * @property int         $id
 * @property int         $idSnapshot
 * @property string      $codigoBarras
 * @property int|null    $idItem
 * @property float       $existencia
 */
class GrumascanSnapshotDetalle extends ActiveRecord
{
    public static function tableName()
    {
        return 'grumascan_snapshot_detalle';
    }

    public function getSnapshot()
    {
        return $this->hasOne(GrumascanSnapshot::class, ['id' => 'idSnapshot']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }
}
