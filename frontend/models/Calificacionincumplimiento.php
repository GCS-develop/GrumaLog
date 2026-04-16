<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Modelo para tabla calificacion_incumplimiento.
 * Registra los incumplimientos de una OC.
 * Cada incumplimiento penaliza la calidad_producto: score efectivo = (score + N) / (1 + N)
 */
class Calificacionincumplimiento extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'calificacion_incumplimiento';
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value'              => new Expression('GETDATE()'),
            ],
            [
                'class'              => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['id_ordendecompra', 'descripcion', 'numero_oc'], 'required'],
            [['id_ordendecompra', 'created_by'], 'integer'],
            [['descripcion'], 'string', 'max' => 500],
            [['numero_oc'], 'string', 'max' => 50],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'id_ordendecompra' => 'Orden de Compra',
            'numero_oc'        => 'N° OC',
            'descripcion'      => 'Descripción del Incumplimiento',
            'created_at'       => 'Fecha Registro',
            'created_by'       => 'Registrado Por',
        ];
    }

    public function getOrdendecompra()
    {
        return $this->hasOne(Ordendecompra::class, ['id' => 'id_ordendecompra']);
    }

    /**
     * Cuenta los incumplimientos registrados para una OC.
     */
    public static function countByOc($idOc)
    {
        return (int)self::find()->where(['id_ordendecompra' => $idOc])->count();
    }

    /**
     * Lista todos los incumplimientos de una OC, más recientes primero.
     */
    public static function listByOc($idOc)
    {
        return self::find()
            ->where(['id_ordendecompra' => $idOc])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}
