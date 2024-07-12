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
    public $fechaDesde;
    public $fechaHasta;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'viewventapos';
    }

    public static function primaryKey()
    {
        return ['id'];
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
            [['fecha', 'fechaDesde', 'fechaHasta'], 'safe'],
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
            'codigoCentroOperacion' => 'Codigo Almacén',
            'nombreCentroOperacion' => 'Almacén',
            'fecha' => 'Fecha',
            'item' => 'Código Item',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripción',
            'color' => 'Color',
            'talla' => 'Talla',
            'subtotal' => 'Subtotal',
            'proveedor' => 'Proveedor',
            'nombreProveedor' => 'Proveedor',
            'unidades' => 'Unidades',
            'preciounitario' => 'Precio Unitario',
            'total' => 'Total',
        ];
    }

    public static function getData ($id){

        $factura = Factura::findOne(['id' => $id]);
        $codigo = $factura->proveedor->codigo;

        $data = Viewventapos::find()->where([
                                                'proveedor' => $codigo,
                                                'fechaDesde' => $factura->fechaDesde,
                                                'fechaHasta' => $factura->fechaHasta
                                            ])->all();

        foreach ($data as $detalle) {

            $model = new Facturadetalle();
            $model->idFactura = $id;
            $model->codigoBarra = $detalle->codigobarra;
            $model->item = $detalle->item;
            $model->color = $detalle->color;
            $model->talla = $detalle->talla;
            $model->motivo = '02';
            $model->unidadMedida = 'UND';
            $model->cantidadBase = $detalle->unidades;
            $model->precioUnitario = $preciounitario;
            $model->bodega = '000';

            $model->save();

        }

    }

    public static function totalEntradas ($codigo, $desde, $hasta){
        $total = Viewventapos::find()
                            ->where(['proveedor' => $codigo]) // Filtrar por el código
                            ->andWhere(['>', 'unidades', 0]) // Condición: atributo_negativo < 0
                            ->andWhere(['between', 'fecha', $desde, $hasta])
                            ->sum('total');

        return $total;
    }

    public static function totalDevoluciones ($codigo, $desde, $hasta){
        $total = Viewventapos::find()
                            ->where(['proveedor' => $codigo]) // Filtrar por el código
                            ->andWhere(['<', 'unidades', 0]) // Condición: atributo_negativo < 0
                            ->andWhere(['between', 'fecha', $desde, $hasta])
                            ->sum('total');

        return $total;
    }
}
