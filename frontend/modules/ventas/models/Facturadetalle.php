<?php

namespace frontend\modules\ventas\models;

use Yii;

use common\models\ProcedimientosGenerales;

/**
 * This is the model class for table "facturadetalle".
 *
 * @property int $id
 * @property int $idFactura
 * @property string $codigoBarra
 * @property string $item
 * @property string $color
 * @property string $talla
 * @property string $unidadMedida
 * @property string $bodega
 * @property string $motivo
 * @property int $cantidadBase
 * @property float $precioUnitario
 *
 * @property Factura $idFactura0
 */
class Facturadetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'facturadetalle';
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
            [['idFactura', 'item', 'color', 'talla', 'cantidadBase', 'precioUnitario'], 'required'],
            [['idFactura', 'cantidadBase', 'error', 'cantidadTotal'], 'integer'],
            [['precioUnitario'], 'number'],
            [['codigoBarra', 'talla'], 'string', 'max' => 50],
            [['item'], 'string', 'max' => 10],
            [['color', 'descripcion'], 'string', 'max' => 150],
            [['unidadMedida', 'bodega'], 'string', 'max' => 5],
            [['motivo'], 'string', 'max' => 2],
            [['tipoMovimiento'], 'string', 'max' => 3],
            [['idFactura'], 'exist', 'skipOnError' => true, 'targetClass' => Factura::class, 'targetAttribute' => ['idFactura' => 'id']],
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
            'color' => 'Color',
            'talla' => 'Talla',
            'unidadMedida' => 'UM',
            'bodega' => 'Bodega',
            'motivo' => 'Motivo',
            'cantidadBase' => 'Unidades',
            'precioUnitario' => 'Precio Unitario',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripción',
        ];
    }

    /**
     * Gets query for [[Factura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Factura::class, ['id' => 'idFactura']);
    }

    public static function grabarItems ($modelfactura, $tienenotacredito = 0){

        $idfactura = $modelfactura->id;

        $result = Facturadetalle::grabarDetalle ($modelfactura, $tienenotacredito);

        $affectedRows = Facturaitem::deleteAll(['idFactura' => $idfactura]);

        $modeldetalle = Facturadetalle::find()
                                    ->select([
                                        'codigoBarra'
                                        , 'item'
                                        , 'referencia'
                                        , 'descripcion'
                                        , 'color'
                                        , 'talla'
                                        , 'precioUnitario'
                                        , 'SUM(cantidadBase) AS cantidadBase' 
                                    ])
                                    ->where(['idFactura' => $idfactura])
                                    ->andWhere(['<>', 'codigoBarra', ''])
                                    ->andWhere(['<>', 'cantidadBase', 0])
                                    ->groupBy([
                                        'codigoBarra'
                                        , 'item'
                                        , 'referencia'
                                        , 'descripcion'
                                        , 'color'
                                        , 'talla'
                                        , 'precioUnitario'
                                    ])
                                    ->orderBy(['codigoBarra' => SORT_ASC])->all();

        foreach($modeldetalle as $detalle){
            $modelitem = new Facturaitem();
            $modelitem->idFactura = $idfactura;
            $modelitem->codigoBarra = $detalle->codigoBarra;
            $modelitem->totalUnidadesFactura = $detalle->cantidadBase;
            $modelitem->totalUnidadesSiesa = 0;
            $modelitem->precioUnitario = $detalle->precioUnitario;
            $modelitem->item = $detalle->item;
            $modelitem->talla = $detalle->talla;
            $modelitem->color = $detalle->color;
            $modelitem->referencia = $detalle->referencia;
            $modelitem->descripcion = $detalle->descripcion;
            $modelitem->save();
        }
    }

    public static function grabarDetalle ($modelfactura, $tienenotacredito = 0){

        $query = Viewventapos::find()
                            ->where(['proveedor' => $modelfactura->proveedor->codigo])
                            ->andWhere(['between', 'fecha', $modelfactura->fechaDesde, $modelfactura->fechaHasta])
                            ->all();

        foreach($query as $detalle){

            $model = new Facturadetalle();
            $model->idFactura = $modelfactura->id;
            $model->codigoBarra = $detalle->codigobarra;
            $model->item = $detalle->item;
            $model->color = $detalle->color;
            $model->talla = $detalle->talla;
            $model->referencia = $detalle->referencia;
            $model->descripcion = $detalle->descripcion;
            $model->precioUnitario = ProcedimientosGenerales::convertirValorTextoNumero($detalle->preciounitario);
            $model->cantidadBase = ProcedimientosGenerales::convertirValorTextoNumero($detalle->unidades);
            $model->fecha = $detalle->fecha;
            $model->tipoMovimiento = "ENT";
            if ($model->cantidadBase < 0){
                $model->tipoMovimiento = "DEV";
            }
            if (!$model->save()){
                var_dump($detalle->unidades);
                var_dump($model->getErrors());
                die("hola");
            };

        }

        $sql = "UPDATE facturadetalle SET error = 2 
                WHERE idFactura = " . $modelfactura->id . 
                " AND (cantidadBase = 0 OR precioUnitario = 0) ";

        $command = Yii::$app->dbVentasPOS->createCommand($sql);
        $result = $command->execute();

        //var_dump($result); die("hola");

        $sql = "UPDATE facturadetalle SET error = 3
                WHERE idFactura = " . $modelfactura->id . 
                " AND (codigoBarra = 0 OR codigoBarra IS NULL) ";

        $command = Yii::$app->dbVentasPOS->createCommand($sql);
        $result = $command->execute();

        return $result;
    }

    public static function totalDocumentoOK ($idfactura){
        $total = Facturadetalle::find()
                            ->where(['idFactura' => $idfactura]) // Filtrar por el código
                            //->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['=', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

    public static function totalDocumentoError ($idfactura){
        $total = Facturadetalle::find()
                            ->where(['idFactura' => $idfactura]) // Filtrar por el código
                            //->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['<>', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

}
