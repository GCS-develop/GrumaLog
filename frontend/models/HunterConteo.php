<?php

namespace app\models;

use frontend\models\Bodegas;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string|null $codigo
 * @property int $id_bodega
 * @property int|null $id_seccion
 * @property int|null $id_ubicacion
 * @property string|null $origen
 * @property int $estado
 * @property int $total_lecturas
 * @property int $total_skus
 * @property int|null $created_by
 * @property string $created_at
 * @property int|null $updated_by
 * @property string $updated_at
 * @property string|null $processed_at
 *
 * @property HunterConteoScan[] $scans
 * @property HunterConteoDetalle[] $detalles
 */
class HunterConteo extends ActiveRecord
{
    public static function tableName()
    {
        return 'hunter_conteo';
    }

    public function rules()
    {
        return [
            [['id_bodega'], 'required'],
            [['id_bodega', 'id_seccion', 'id_ubicacion', 'estado', 'total_lecturas', 'total_skus', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'processed_at'], 'safe'],
            [['codigo'], 'string', 'max' => 30],
            [['origen'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'codigo'         => 'Código',
            'id_bodega'      => 'Bodega',
            'id_seccion'     => 'Sección',
            'id_ubicacion'   => 'Ubicación',
            'origen'         => 'Origen',
            'estado'         => 'Estado',
            'total_lecturas' => 'Total lecturas',
            'total_skus'     => 'Total SKUs',
            'created_by'     => 'Creado por',
            'created_at'     => 'Creado el',
            'updated_by'     => 'Actualizado por',
            'updated_at'     => 'Actualizado el',
            'processed_at'   => 'Procesado el',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $userId = !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $now    = date('Y-m-d H:i:s');

        if ($insert) {
            if ($this->created_at === null) {
                $this->created_at = $now;
            }
            if ($this->created_by === null) {
                $this->created_by = $userId;
            }
        }

        $this->updated_at = $now;
        $this->updated_by = $userId;

        return true;
    }

    // Relaciones
    public function getScans()
    {
        return $this->hasMany(HunterConteoScan::class, ['id_conteo' => 'id']);
    }

    public function getDetalles()
    {
        return $this->hasMany(HunterConteoDetalle::class, ['id_conteo' => 'id']);
    }

    public function getSeccion()
    {
        return $this->hasOne(HunterSeccion::class, ['id' => 'id_seccion']);
    }

    public function getUbicacion()
    {
        return $this->hasOne(HunterUbicacion::class, ['id' => 'id_ubicacion']);
    }

    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'id_bodega']);
    }
}
