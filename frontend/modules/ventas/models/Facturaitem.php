<?php

namespace frontend\modules\ventas\models;

use Yii;

use common\models\InventariosWs;

/**
 * This is the model class for table "facturaitem".
 *
 * @property int $id
 * @property int $idFactura
 * @property string $codigoBarra
 * @property string $item
 * @property string|null $referencia
 * @property string|null $descripcion
 * @property string $color
 * @property string $talla
 * @property float $precioUnitario
 * @property int $totalUnidadesFactura
 * @property int|null $totalUnidadesSiesa
 */
class Facturaitem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'facturaitem';
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
            [['idFactura', 'codigoBarra', 'item', 'color', 'talla', 'precioUnitario'], 'required'],
            [['idFactura', 'totalUnidadesFactura', 'totalUnidadesSiesa'], 'integer'],
            [['precioUnitario'], 'number'],
            [['codigoBarra', 'talla'], 'string', 'max' => 50],
            [['item'], 'string', 'max' => 10],
            [['referencia', 'descripcion', 'color'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idFactura' => 'Id Factura',
            'codigoBarra' => 'Codigo Barra',
            'item' => 'Item',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripcion',
            'color' => 'Color',
            'talla' => 'Talla',
            'precioUnitario' => 'Precio Unitario',
            'totalUnidadesFactura' => 'Total Unidades Factura',
            'totalUnidadesSiesa' => 'Total Unidades Siesa',
        ];
    }

    public static function numeroItems ($idfactura){
        $total = Facturaitem::find()
                    ->where(['idFactura' => $idfactura])
                    ->count();

        return $total;
    }

    public static function totalPesos ($idfactura){
        $total = Facturaitem::find()
                    ->where(['idFactura' => $idfactura])
                    ->sum('precioUnitario * totalUnidadesFactura');

        return $total;
    }

    public static function buscarBodega ($idfactura){

        $inventario = new InventariosWs();

        ini_set('memory_limit', '8G'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)
        ini_set('max_execution_time', '7200'); //300 seconds = 5 minutes

        $bodega = array();

        $modeldetalle = Facturaitem::find()
                                    ->select([
                                        'id'
                                        , 'codigoBarra'
                                        , 'item'
                                        , 'referencia'
                                        , 'descripcion'
                                        , 'color'
                                        , 'talla'
                                        , 'precioUnitario'
                                        , 'totalUnidadesFactura' 
                                    ])
                                    ->where(['idFactura' => $idfactura])
                                    ->andWhere(['<>', 'codigoBarra', ''])
                                    ->andWhere(['OR', ['<>', 'totalUnidadesFactura', 'totalUnidadesSiesa'], ['totalUnidadesSiesa' => 0]])
                                    ->orderBy(['codigoBarra' => SORT_ASC])->all();

        $affectedRows = Transferencia::deleteAll(['idFactura' => $idfactura]);

        foreach($modeldetalle as $detalle){
            
            $ean = $detalle->codigoBarra;

            $responseData = $inventario->getAllInventariosSiesa ($ean);

            if (!is_array($responseData)){
                continue;
            }

            $cantidadtotal = $detalle->totalUnidadesFactura;
            $totalUnidades = 0;

            foreach($responseData as $data){
                $salir = false;
                if (isset($data["CantidadDisponible"])) {
                    if ($data["CantidadDisponible"] > 0){

                        if ($data["CantidadDisponible"] >= $cantidadtotal){
                            $cantidad = $cantidadtotal;
                            $salir = true;
                        }else{
                            $cantidadtotal = $cantidadtotal - $data["CantidadDisponible"];
                            $cantidad = $data["CantidadDisponible"];
                        }

                        $totalUnidades = $totalUnidades + $cantidad;

                        $item = new Transferencia();
                        $item->idFactura = $idfactura;
                        $item->bodega = $data['Bodega'];
                        $item->codigoBarra = $ean;
                        $item->cantidadBase = $cantidad;
                        $item->precioUnitario = $detalle->precioUnitario;
                        $item->item = $detalle->item;
                        $item->talla = $detalle->talla;
                        $item->color = $detalle->color;
                        $item->unidadMedida = 'UND';
                        $item->motivo = '02';
                        $item->referencia = $detalle->referencia;
                        $item->descripcion = $detalle->descripcion;
                        $item->save();

                        if ($salir){
                            break;
                        }
                    }
                }
            }

            $modelitem = Facturaitem::findOne(['id' => $detalle->id]);
            $modelitem->totalUnidadesSiesa = $totalUnidades;
            $modelitem->error = 0;

            if ($modelitem->totalUnidadesFactura <>  $modelitem->totalUnidadesSiesa){
                $modelitem->error = 2;
            }

            $modelitem->save();
        }
    }

}
