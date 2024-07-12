<?php

namespace frontend\modules\ventas\models;

use Yii;

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
            [['idFactura', 'codigoBarra', 'item', 'color', 'talla', 'bodega', 'cantidadBase', 'precioUnitario'], 'required'],
            [['idFactura', 'cantidadBase', 'error', 'cantidadTotal'], 'integer'],
            [['precioUnitario'], 'number'],
            [['codigoBarra', 'talla'], 'string', 'max' => 50],
            [['item'], 'string', 'max' => 10],
            [['color', 'descripcion'], 'string', 'max' => 150],
            [['unidadMedida', 'bodega'], 'string', 'max' => 5],
            [['motivo'], 'string', 'max' => 2],
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

        $operador = '>';
        if ($modelfactura->tipoDocumento == 'DCG'){
            $operador = '<';
        }

        $query = Viewventapos::find()->alias('vi');
                            
        $query->select([
                'vi.codigobarra',
                'vi.item', 
                'vi.color', 
                'vi.talla', 
                'vi.referencia', 
                'vi.descripcion',
                'IFNULL(vi.preciounitario, 0) AS precioUnitario',
                'SUM(vi.unidades) AS cantidadBase'
        ]);

        $query->groupBy(['vi.codigobarra', 'vi.item', 'vi.color', 'vi.talla', 'vi.referencia',
                        'vi.descripcion', 'vi.preciounitario']
                )
                ->asArray()
                ->all();

        foreach ($query as $result) {
            var_dump($result);
            die("hola");
            $model = new Facturadetalle();
            $model->idFactura = $modelfactura->id;
            $model->codigoBarra = $result['codigobarra'];
            $model->item = $result['item'];
            $model->referencia = $result['referencia'];
            $model->descripcion = $result['descripcion'];
            $model->color = $result['color'];
            $model->talla = $result['talla'];
            $model->unidadMedida = 'UND';
            $model->motivo = '02';
            $model->precioUnitario = $result['precioUnitario'];
            $model->cantidadBase = $result['cantidadBase'];
            $model->error = 1;
            $model->cantidadTotal = 0;
            $model->save();
         }

        /*$select = "
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
                SUM(vi.unidades) AS cantidadBase
            FROM 
                viewventapos vi ";

        $where = "
            WHERE 
                vi.proveedor = '" . $modelfactura->proveedor->codigo . "'" .
                "AND vi.fecha BETWEEN '" . $modelfactura->fechaDesde . "' AND '"  . $modelfactura->fechaHasta . "' ";
                
        if ($tienenotacredito == 1){
            $where = $where . " AND vi.unidades " . $operador . " 0";
        }

        $group = " GROUP BY 1,2,3,4,5,6,7,8,9,10,11";

        $sql = $select . $where . $group;

        // Ejecutar el query SQL
        $command = Yii::$app->dbVentasPOS->createCommand($sql);

        // Ejecutar el comando SQL
        $result = $command->execute();*/

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
                            ->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['=', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

    public static function totalDocumentoError ($idfactura){
        $total = Facturadetalle::find()
                            ->where(['idFactura' => $idfactura]) // Filtrar por el código
                            ->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['<>', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

}
