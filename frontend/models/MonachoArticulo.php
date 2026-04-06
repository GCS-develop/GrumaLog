<?php

namespace frontend\models;

/**
 * Artículo dentro de un Pedido Monacho.
 *
 * @property int    $id
 * @property int    $pedido_id
 * @property int    $codigo
 * @property string $descripcion
 * @property string $referencia
 * @property string $estilo1 ... $estilo5
 * @property string $concepto
 * @property string $consumidor
 * @property string $universo
 * @property string $prenda
 * @property string $tendencia
 * @property float  $costo
 * @property float  $precio_venta
 * @property float  $margen
 * @property string $rango
 * @property float  $pvp_mayorista
 */
class MonachoArticulo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'monacho_articulo';
    }

    public function rules()
    {
        return [
            [['pedido_id', 'descripcion'], 'required'],
            [['pedido_id', 'codigo'], 'integer'],
            [['codigo'], 'default', 'value' => null],
            [['costo', 'precio_venta', 'margen', 'pvp_mayorista'], 'number'],
            [['descripcion'], 'string', 'max' => 300],
            [['referencia', 'estilo1', 'estilo2', 'estilo3', 'estilo4', 'estilo5',
              'concepto', 'consumidor', 'universo', 'prenda', 'tendencia'], 'string', 'max' => 100],
            [['rango'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'pedido_id'    => 'Pedido',
            'codigo'       => 'Código SIESA',
            'descripcion'  => 'Descripción',
            'referencia'   => 'Referencia Proveedor',
            'costo'        => 'Costo',
            'precio_venta' => 'Precio Venta',
            'rango'        => 'Rango',
        ];
    }

    public function getDetalles()
    {
        return $this->hasMany(MonachoDetalle::class, ['articulo_id' => 'id']);
    }

    public function getPedido()
    {
        return $this->hasOne(MonachoPedido::class, ['id' => 'pedido_id']);
    }

    /** Colores únicos con sus detalles agrupados */
    public function getColoresAgrupados()
    {
        $detalles = $this->detalles;
        $colores  = [];
        foreach ($detalles as $d) {
            $colores[$d->color][] = $d;
        }
        return $colores;
    }

    /** Total unidades del artículo */
    public function getTotalUds()
    {
        return (int) MonachoDetalle::find()
            ->where(['articulo_id' => $this->id])
            ->sum('cantidad');
    }
}
