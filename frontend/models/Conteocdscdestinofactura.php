<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;
use diecoding\barcode\generator\Barcode;

/**
 * This is the model class for table "conteocdscdestinofactura".
 *
 * @property int $id
 * @property int $idProveedor
 * @property string $numeroFactura
 * @property string $fecha
 * @property int|null $idCentroOperacionLegaliza
 * @property int $totalUnidades
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestino[] $conteocdscdestinos
 * @property Centrooperacion $centroOperacionLegaliza
 * @property Proveedor $proveedor
 */
class Conteocdscdestinofactura extends \yii\db\ActiveRecord
{
    public $almacen;
    public $codigoAlmacen;
    public $codigoProveedor;
    public $nit;
    public $razonSocial;
    public $tipoProveedor;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscdestinofactura';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['idProveedor', 'numeroFactura', 'fecha', 'totalUnidades'], 'required'],
            [['idProveedor', 'idCentroOperacionLegaliza', 'totalUnidades', 'created_by', 'updated_by',
            'idEstadoEntrada', 'idEstadoTraspaso', 'idUserLegaliza', 'idUserEntrada', 'idUserTraspaso',
            'idSerieEntrada', 'numeroEntrada', 'idOrdenCompra'], 'integer'],
            [['fecha', 'created_at', 'updated_at', 'observacionLegalizacion', 'fechaLegaliza',
            'fechaEntrada', 'fechaTraspaso'], 'safe'],
            [['numeroFactura'], 'string', 'max' => 20],
            [['idProveedor', 'numeroFactura'], 'unique', 'targetAttribute' => ['idProveedor', 'numeroFactura']],
            [['idProveedor'], 'exist', 'skipOnError' => true, 'targetClass' => Proveedor::class, 'targetAttribute' => ['idProveedor' => 'id']],
            [['idCentroOperacionLegaliza'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idCentroOperacionLegaliza' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Radicado',
            'idProveedor' => 'Id Proveedor',
            'numeroFactura' => 'Número Orden Compra',
            'fecha' => 'Fecha',
            'idCentroOperacionLegaliza' => 'CO Legaliza',
            'totalUnidades' => 'Total Unidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idEstado' => 'Estado',
            'idLegalizado' => 'Legalizado',
            'codigoProveedor' => 'Cod. Proveedor',
            'razonSocial' => 'Proveedor',
            'idEstadoEntrada' => 'Entrada',
            'idEstadoTraspaso' => 'Traspaso',
            'idUserLegaliza' => 'Usuario Legaliza',
            'fechaLegaliza' => 'Fecha Legaliza',
            'idSerieEntrada' => 'Serie',
            'numeroEntrada' => 'Numero',
            'idUserEntrada' => 'Usuario Entrada',
            'idUserTraspaso' => 'Usuario Traspaso'
        ];
    }

    /**
     * Gets query for [[Conteocdscdestinos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConteocdscdestinos()
    {
        return $this->hasMany(Conteocdscdestino::class, ['idConteocdscdestinofactura' => 'id']);
    }

    /**
     * Gets query for [[CentroOperacionLegaliza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentroOperacionLegaliza()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idCentroOperacionLegaliza']);
    }

    public function getUserlegaliza()
    {
        return $this->hasOne(User::class, ['id' => 'idUserLegaliza']);
    }

    public function getUserentrada()
    {
        return $this->hasOne(User::class, ['id' => 'idUserEntrada']);
    }

    public function getUsertraspaso()
    {
        return $this->hasOne(User::class, ['id' => 'idUserTraspaso']);
    }

    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idSerieEntrada']);
    }

    /**
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProveedor()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'idProveedor']);
    }

    public function getOrdenCompra()
    {
        return $this->hasOne(Ordendecompra::class, ['id' => 'idOrdenCompra']);
    }

    public static function generarDataConteoCurvas ($radicado, $idproveedor, $numerofactura) {

        $informaciondataconteo = self::generarQuery ($radicado, $idproveedor, $numerofactura);

        $arrayresultado = self::generarArregloCurvas ($informaciondataconteo);


        return $arrayresultado;
    }

    public static function grabarOrdenCompra ($model){
        $respuesta = false;

        $modeloc = OrdendeCompra::findOne(['id' => $model->idOrdenCompra]);

        $modelfactura = new Conteocdscdestinofactura();
        $modelfactura->idProveedor = $modeloc->idProveedor;
        $modelfactura->idOrdenCompra = $model->idOrdenCompra;
        $modelfactura->numeroFactura = $model->numeroOrdenCompra;
        $modelfactura->fecha = $model->fechaOrden;
        $modelfactura->idEstado = 0;
        $modelfactura->idLegalizado = 0;
        $modelfactura->idEstadoEntrada = 0;
        $modelfactura->idEstadoTraspaso = 0;
        $modelfactura->totalUnidades = 0;

        if ($modelfactura->save()){
            $respuesta = true;
        }

        return $respuesta;
    }

    public static function generarQuery ($radicado, $idproveedor, $numerofactura)
    {
        $sql = "
            SELECT 
                fact.id AS radicado, 
                dest.id AS numeroConteo, 
                fact.numeroFactura,
                fact.idProveedor, 
                prv.razonSocial, 
                prv.nit,
                it.item, 
                it.idColor, 
                it.descripcion, 
                col.codigo AS color, 
                tal.codigo AS talla, 
                tal.orden,
                SUM(det.totalUnidades) AS unidades
            FROM conteocdscdestinodetalle det 
            INNER JOIN item it ON det.idItem = it.id
            INNER JOIN talla tal ON it.idTalla = tal.id
            INNER JOIN color col ON it.idColor = col.id
            INNER JOIN categoria cat ON it.idCategoria = cat.id 
            INNER JOIN conteocdscdestino dest ON det.idConteocdscdestino = dest.id
            INNER JOIN conteocdscdestinofactura fact ON dest.idConteocdscdestinofactura = fact.id
            INNER JOIN proveedor prv ON fact.idProveedor = prv.id  
            WHERE 1 = 1
        ";

        if ($radicado != null){
            $sql = $sql . " AND fact.id = :radicado";
        }

        if (($idproveedor != null) && ($numerofactura != null)){
            $sql = $sql . " AND fact.idProveedor = :idproveedor AND fact.numeroFactura = :numerofactura";
        }

        $sql = $sql . "
                GROUP BY fact.id, dest.id, fact.numeroFactura, fact.idProveedor, 
                        prv.razonSocial, prv.nit,it.item, it.idColor, it.descripcion, 
                        col.codigo, tal.codigo, tal.orden 
                ORDER BY it.item, tal.orden";
                // ORDER BY it.item, col.codigo";

        $data = self::getDb()->createCommand($sql, [
                                                        ':radicado' => $radicado,
                                                        ':idproveedor' => $idproveedor,
                                                        ':numerofactura' => $numerofactura
                                                    ])->queryAll();

        return $data;
    }

    public static function generarArregloCurvas ($filasConsulta){

        $filas = [];

        foreach ($filasConsulta as $fila) {
            // Crear un identificador de fila único
            $identificador = $fila['radicado'] . '-' . $fila['item'] . '-' . $fila['descripcion'] . '-' . $fila['color'];

            // Verificar si la fila ya existe en el array
            if (!isset($filas[$identificador])) {
                
                // Si no existe, crear la fila con los valores predeterminados
                $filas[$identificador] = [
                    'radicado' => $fila['radicado'],
                    'item' => $fila['item'],
                    'descripcion' => $fila['descripcion'],
                    'color' => $fila['color'],
                    'totalUnidadesConteo' => 0,
                ];
            }

            // Sumar las unidades de conteo a las totales de la fila
            $filas[$identificador]['totalUnidadesConteo'] += $fila['unidades'];

            $unidades = 0;
            if (isset($filas[$identificador][$fila['talla']]['unidades'])){
                $unidades = $filas[$identificador][$fila['talla']]['unidades'];
            }
        
            // Agregar los demás valores a la fila
            // Puedes agregar aquí las demás columnas que quieras incluir en la fila
            $filas[$identificador][$fila['talla']] = [
                'unidades' => $fila['unidades'] + $unidades,
            ];
        }

        //die("Fin");

        return $filas;
    }

    /*public static function printTraspaso ($idconteofactura, $idcentrooperacion = null){

        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $modelparametros = ParametrosControl::findOne(['codigo' => '001']);
        $nombreEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '002']);
        $nitEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '003']);
        $direccionEmpresa = $modelparametros->valor;

        $modelparametros = ParametrosControl::findOne(['codigo' => '004']);
        $telefonoEmpresa = $modelparametros->valor;

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
                                    ->andFilterWhere([
                                        'idConteocdscdestinofactura' => $idconteofactura,
                                        'idCentroOperacion' => $idcentrooperacion,
                                    ])->all();;

        $contentAll = "";

        foreach($dataProviderDestino as $destino){

            $serie = $destino->factura->tipodocumento->codigo;
            $numero = $destino->factura->numeroEntrada;
            $codigoalmacenlegaliza = $destino->factura->centroOperacionLegaliza->codigo;
            $nombrealmacenlegaliza = $destino->factura->centroOperacionLegaliza->nombre;
            $fechatraspaso = date("d/m/Y", strtotime($destino->factura->fechaTraspaso));

            $content = '<h2>TRASPASO MERCANCIA</h2>' ;
            $content .= $nombreEmpresa . "<br>";
            $content .= $nitEmpresa . "<br>";
            $content .= $direccionEmpresa . "&nbsp;&nbsp;Tel: " . $telefonoEmpresa . "<br>";
    
            $content .= '<div style="width: 340px; height: 5px; background-color: #fff; border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;"></div>';

            $detalle = "<table>";
            $detalle .= "<tr>";
    
            $data = "<td style='width: 100px;'>" . "SERIE: " . $serie . "</td>";
            $detalle .= $data;
            $data = "<td style='width: 130px;'>" . "NUMERO: " . $numero . "</td>";
            $detalle .= $data;
            $data = "<td style='width: 100px;'>" . "CAJA: " . "KPM" . "</td>";
            $detalle .= $data;
    
            $detalle .= "</tr>";
            $detalle .= "</table><br>" ;
            
            $detalle .= "ALMACEN ORIGEN: " . $codigoalmacenlegaliza . "<br>";
            $detalle .= $nombrealmacenlegaliza . "<br>";

            $detalle .= "ALMACEN DESTINO: " . $destino->centrooperacion->codigo . "<br>";
            $detalle .= $destino->centrooperacion->nombre . "<br><br>";

            $detalle .= "FECHA TRASPASO: " . $fechatraspaso . "<br>";

            $detalle .= '<div style="width: 340px; height: 5px; background-color: #fff; border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;"></div>';

            $detalle .= "<table>";
            $detalle .= "<tr>";
    
            $data = "<td style='width: 100px; font-size: 20px;'>" . "REF" . "</td>";
            $detalle .= $data;
            $data = "<td style='width: 130px; font-size: 20px;'>" . "DESCRIPCION" . "</td>";
            $detalle .= $data;
            $data = "<td style='width: 100px; text-align: right; font-size: 20px;'>" . "UNDS" . "</td>";
            $detalle .= $data;

            $detalle .= "</tr>";
            $detalle .= "</table><br>" ;

            $detalle .= '<div style="width: 340px; height: 5px; background-color: #fff; border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;"></div>';

            $detalle .= "<table>";
            $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                                        ->where(['>', 'totalUnidades', 0])
                                        ->all();

            foreach($dataProviderDetalle as $referencia){
                $detalle .= "<tr>";

                $data = "<td style='width: 50px;'>" . $referencia->item->item . "</td>";
                $detalle .= $data;

                $data = "<td style='width: 120px;'>" . 'C:&nbsp;' . $referencia->item->color->codigo . "</td>";
                $detalle .= $data;

                $data = "<td style='width: 120px;'>" . 'T:&nbsp;' . $referencia->item->talla->codigo . "</td>";
                $detalle .= $data;

                $data = "<td style='width: 50px;'>" . $referencia->totalUnidades . "</td>";
                $detalle .= $data;

                $detalle .= "<tr>";
                $data = "<td colspan='4'>" . $referencia->item->descripcion . "</td>";
                $detalle .= $data;
                $detalle .= "</tr>";

                $detalle .= "</tr>";
            }

            $detalle .= "</table><br><br><br><br><br>";

            $detalle .= "<table style='border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;'";
            $detalle .= "<tr>";

            $data = "<td style='width: 290px; font-weight: bold; font-size: 20px;'>" . 'TOTAL UNIDADES' . "</td>";
            $detalle .= $data;

            $data = "<td style='width: 50px;'>" . $destino->total . "</td>";
            $detalle .= $data;

            $detalle .= "</tr>";
            $detalle .= "</table><br>";

            $detalle .= "1 SERIE DOCUMENTO" . "<br>";
            // CODE128 (auto) is the default mode
            $codigobarras = Barcode::widget([
                'value' => $serie,
                'options' => [
                    'style' => "width: 4cm; height: 1cm;",
                ],
                'pluginOptions' => [
                    'ean128' => true,
                ]
            ]);

            $detalle .= $codigobarras . '<br>';

            $detalle .= "2 NUMERO DOCUMENTO" . "<br>";
            $codigobarras = Barcode::widget([
                'value' => $numero,
                'options' => [
                    'style' => "width: 4cm; height: 1cm;",
                ],
                'pluginOptions' => [
                    'ean128' => true,
                ]
            ]);

            $detalle .= $codigobarras . '<br>';

            //$detalle .= $nombrealmacenlegaliza . "<br>";

            $detalle .= '<h6>Alm destino:' . $destino->centrooperacion->codigo . '-' . $destino->centrooperacion->nombre . "</h6><br>";

            $detalle .= "<table>";
            $detalle .= "<tr>";
    
            $data = "<td colspan='3' style='width: 250px;'>" . "</td>";
            $detalle .= $data;
            $data = "<td style='width: 100px;'>" . "FIRMA SELLO:" . "</td>";
            $detalle .= $data;
    
            $detalle .= "</tr>";
            $detalle .= "</table><br>" ;

            $content .= $detalle;

            $content = $this->renderPartial('recibo', [
                'nombre' => $recibo['nombre'],
                'monto' => $recibo['monto'],
            ]);

            $contentAll .= $content;
        }

        return $contentAll;
    }*/
}
