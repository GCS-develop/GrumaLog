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
            if ($model->cantidadBase > 0){
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

    public static function grabarItems1 ($modelfactura, $tienenotacredito = 0){

        $operador = '>';
        if ($modelfactura->tipoDocumento == 'DCG'){
            $operador = '<';
        }

        $select = "
            INSERT INTO facturadetalle (idFactura, codigoBarra, item, color, talla, unidadMedida,
                                         bodega, motivo, referencia, descripcion, precioUnitario, 
                                         cantidadBase, fecha, tipoMovimiento)
            SELECT " .
                $modelfactura->id . " AS idFactura,
                vi.codigobarra,
                vi.item, 
                vi.color, 
                vi.talla, 
                'UND' AS unidadMedida,
                '' AS bodega,
                '02' AS motivo,
                vi.referencia, 
                vi.descripcion,
                IFNULL(vi.preciounitario, 0) AS precioUnitario,
                vi.unidades AS cantidadBase,
                vi.fecha,
                IF(vi.unidades < 0, 'DEV', 'ENT') AS tipoMovimiento
            FROM 
                viewventapos vi ";

        $where = "
            WHERE 
                vi.proveedor = '" . $modelfactura->proveedor->codigo . "'" .
                "AND vi.fecha BETWEEN '" . $modelfactura->fechaDesde . "' AND '"  . $modelfactura->fechaHasta . "' ";
                
        /*if ($tienenotacredito == 1){
            $where = $where . " AND vi.unidades " . $operador . " 0";
        }*/

        //$group = " GROUP BY 1,2,3,4,5,6,7,8,9,10,11";

        //$sql = $select . $where . $group;

        $sql = $select . $where;

        // Ejecutar el query SQL
        $command = Yii::$app->dbVentasPOS->createCommand($sql);

        // Ejecutar el comando SQL
        $result = $command->execute();

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
