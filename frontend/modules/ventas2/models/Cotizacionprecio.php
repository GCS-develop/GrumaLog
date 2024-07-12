<?php

namespace frontend\modules\ventas\models;

use Yii;

/**
 * This is the model class for table "cotizacionprecio".
 *
 * @property int $id
 * @property string|null $codigoProveedor
 * @property float|null $nitProveedor
 * @property string|null $razonSocial
 * @property float|null $item
 * @property string|null $descripcion
 * @property string|null $color
 * @property string|null $talla
 * @property string|null $fechaactivacion
 * @property string|null $unidad
 * @property string|null $moneda
 * @property float|null $preciounitario
 * @property float|null $tiempoentrega
 * @property string|null $fechahasta
 */
class Cotizacionprecio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cotizacionprecio';
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
            [['nitProveedor', 'item', 'preciounitario', 'tiempoentrega'], 'number'],
            [['fechaactivacion'], 'safe'],
            [['codigoProveedor'], 'string', 'max' => 150],
            [['razonSocial', 'color', 'talla'], 'string', 'max' => 45],
            [['descripcion'], 'string', 'max' => 250],
            [['unidad', 'fechahasta'], 'string', 'max' => 10],
            [['moneda'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoProveedor' => 'Código Proveedor',
            'nitProveedor' => 'Nit Proveedor',
            'razonSocial' => 'Razón Social',
            'item' => 'Item',
            'descripcion' => 'Descripción',
            'color' => 'Color',
            'talla' => 'Talla',
            'fechaactivacion' => 'Fecha Activación',
            'unidad' => 'Unidad',
            'moneda' => 'Moneda',
            'preciounitario' => 'Precio Unitario',
            'tiempoentrega' => 'Tiempoentrega',
            'fechahasta' => 'Fechahasta',
        ];
    }
}
