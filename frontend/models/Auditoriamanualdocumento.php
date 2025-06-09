<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;

/**
 * This is the model class for table "auditoriamanualdocumento".
 *
 * @property int $id
 * @property string $codigoBodegaSalida
 * @property string $numeroDocumento
 * @property string $fecha
 * @property string|null $notasDocumento
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Auditoriamanualdocumento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auditoriamanualdocumento';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigoBodegaSalida', 'numeroDocumento', 'fecha', 'idInterfase'], 'required'],
            [['fecha', 'created_at', 'updated_at', 'fechaRegistra'], 'safe'],
            [['created_by', 'updated_by', 'registrada', 'usuarioRegistra'], 'integer'],
            [['codigoBodegaSalida'], 'string', 'max' => 5],
            [['numeroDocumento'], 'string', 'max' => 20],
            [['notasDocumento'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoBodegaSalida' => 'Cod. Bodega Salida',
            'numeroDocumento' => 'Número Documento',
            'fecha' => 'Fecha',
            'notasDocumento' => 'Notas Documento',
            'idInterfase' => 'ID Interfase',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getBodegasalida()
    {
        return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodegaSalida']);
    }

    public function getUsuarioregistra()
    {
        return $this->hasOne(User::class, ['id' => 'usuarioRegistra']);
    }
}
