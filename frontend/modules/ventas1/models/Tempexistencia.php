<?php

namespace frontend\modules\ventas\models;

use Yii;

/**
 * This is the model class for table "tempexistencia".
 *
 * @property int $id
 * @property string $codigoBodega
 * @property string $codigoEAN
 * @property int $cantidadDisponible
 * @property int $cantidadSolicitada
 * @property int $cantidadTotal
 */
class Tempexistencia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tempexistencia';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbVentasPOS');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idFactura','codigoBodega', 'codigoEAN', 'cantidadTotal'], 'required'],
            [['cantidadDisponible', 'cantidadSolicitada', 'cantidadTotal', 'numero', 'idFactura'], 'integer'],
            [['codigoBodega'], 'string', 'max' => 5],
            [['codigoEAN'], 'string', 'max' => 45],
            [['codigoBodega', 'codigoEAN'], 'unique', 'targetAttribute' => ['codigoBodega', 'codigoEAN']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoBodega' => 'Codigo Bodega',
            'codigoEAN' => 'Codigo Ean',
            'cantidadDisponible' => 'Cantidad Disponible',
            'cantidadSolicitada' => 'Cantidad Solicitada',
            'cantidadTotal' => 'Cantidad Total',
        ];
    }
}
