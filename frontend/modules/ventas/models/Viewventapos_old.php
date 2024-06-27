<?php

namespace frontend\modules\ventas\models;

use Yii;

/**
 * This is the model class for table "viewventapos".
 *
 * @property int $id
 * @property string|null $codigoCentroOperacion
 * @property string $nombreCentroOperacion
 * @property string $fecha
 * @property string $item
 * @property string|null $referencia
 * @property string|null $descripcion
 * @property string|null $color
 * @property string|null $talla
 * @property float|null $subtotal
 * @property string $proveedor
 * @property string|null $nombreProveedor
 * @property float $unidades
 * @property float|null $preciounitario
 * @property float|null $total
 */
class Viewventapos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viewventapos';
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
            [['id'], 'integer'],
            [['nombreCentroOperacion', 'fecha', 'item', 'proveedor', 'unidades'], 'required'],
            [['fecha'], 'safe'],
            [['subtotal', 'unidades', 'preciounitario', 'total'], 'number'],
            [['codigoCentroOperacion'], 'string', 'max' => 5],
            [['nombreCentroOperacion', 'referencia', 'descripcion'], 'string', 'max' => 150],
            [['item'], 'string', 'max' => 20],
            [['color', 'talla'], 'string', 'max' => 45],
            [['proveedor', 'nombreProveedor'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoCentroOperacion' => 'Codigo Centro Operacion',
            'nombreCentroOperacion' => 'Nombre Centro Operacion',
            'fecha' => 'Fecha',
            'item' => 'Item',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripcion',
            'color' => 'Color',
            'talla' => 'Talla',
            'subtotal' => 'Subtotal',
            'proveedor' => 'Proveedor',
            'nombreProveedor' => 'Nombre Proveedor',
            'unidades' => 'Unidades',
            'preciounitario' => 'Preciounitario',
            'total' => 'Total',
        ];
    }
}
