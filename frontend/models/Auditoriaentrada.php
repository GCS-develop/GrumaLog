<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "auditoriaentrada".
 *
 * @property int $id
 * @property int $idTransferenciaerp
 * @property string|null $centroOperacionOrdenCompra
 * @property string|null $tipoDocumentoOrdenCompra
 * @property int $consecutivoOrdenCompra
 * @property string|null $descripcion
 * @property int $idestado
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Transferenciaerp $transferenciaerp
 * @property Auditoriaentradadetalle[] $detalles
 */
class Auditoriaentrada extends \yii\db\ActiveRecord
{
    const ESTADO_ABIERTA    = 1;
    const ESTADO_FINALIZADA = 2;

    public static function tableName()
    {
        return 'auditoriaentrada';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['idTransferenciaerp', 'consecutivoOrdenCompra'], 'required'],
            [['idTransferenciaerp', 'consecutivoOrdenCompra', 'idestado', 'created_by', 'updated_by'], 'integer'],
            [['centroOperacionOrdenCompra', 'tipoDocumentoOrdenCompra'], 'string', 'max' => 10],
            [['descripcion'], 'string', 'max' => 255],
            [['idestado'], 'default', 'value' => self::ESTADO_ABIERTA],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                         => 'ID',
            'idTransferenciaerp'         => 'Transferencia ERP',
            'centroOperacionOrdenCompra' => 'CO',
            'tipoDocumentoOrdenCompra'   => 'Tipo Doc OC',
            'consecutivoOrdenCompra'     => 'Consecutivo OC',
            'descripcion'               => 'Descripción',
            'idestado'                  => 'Estado',
            'created_at'                => 'Creado',
            'created_by'                => 'Creado Por',
        ];
    }

    public function getTransferenciaerp()
    {
        return $this->hasOne(Transferenciaerp::class, ['id' => 'idTransferenciaerp']);
    }

    public function getDetalles()
    {
        return $this->hasMany(Auditoriaentradadetalle::class, ['idauditoriaentrada' => 'id']);
    }

    public function getEstadoLabel()
    {
        return $this->idestado == self::ESTADO_FINALIZADA ? 'Finalizada' : 'Abierta';
    }
}
