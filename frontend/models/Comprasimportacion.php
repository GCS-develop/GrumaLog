<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use common\models\User;

/**
 * This is the model class for table "comprasimportacion".
 *
 * @property int $id
 * @property int|null $numeroRegistros
 * @property float|null $totalUnidades
 * @property int|null $estadoSiesa      (NULL=sin enviar, 1=exitoso, 0=error)
 * @property string|null $mensajeSiesa
 * @property string|null $tipoDocSiesa      Tipo documento creado en SIESA (ej: 2CA)
 * @property string|null $numDocSiesa       Consecutivo del documento en SIESA (ej: 6544)
 * @property string|null $jsonEnviadoSiesa  JSON payload enviado a SIESA
 * @property string|null $jsonRespuestaSiesa JSON respuesta de SIESA
 * @property int|null $estadoCarvajal   (NULL=sin enviar, 1=exitoso, 0=error)
 * @property string|null $mensajeCarvajal
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Comprasimportacion extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'comprasimportacion';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function () {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    public function rules()
    {
        return [
            [['numeroRegistros', 'created_by', 'updated_by', 'estadoSiesa', 'estadoCarvajal'], 'integer'],
            [['totalUnidades'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['mensajeSiesa', 'mensajeCarvajal'], 'string', 'max' => 500],
            [['tipoDocSiesa'], 'string', 'max' => 10],
            [['numDocSiesa'],  'string', 'max' => 20],
            [['jsonEnviadoSiesa', 'jsonRespuestaSiesa'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'numeroRegistros' => 'Registros',
            'totalUnidades'   => 'Total Unidades',
            'estadoSiesa'     => 'Estado SIESA',
            'mensajeSiesa'    => 'Mensaje SIESA',
            'tipoDocSiesa'    => 'Tipo Doc SIESA',
            'numDocSiesa'     => 'Nro Doc SIESA',
            'estadoCarvajal'  => 'Estado Carvajal',
            'mensajeCarvajal' => 'Mensaje Carvajal',
            'created_at'      => 'Fecha Carga',
            'created_by'      => 'Usuario',
        ];
    }

    public function getDetalles()
    {
        return $this->hasMany(Comprasimportaciondetalle::class, ['idImportacion' => 'id']);
    }

    public function getUsuariocrea()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
