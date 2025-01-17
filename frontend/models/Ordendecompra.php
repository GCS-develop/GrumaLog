<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\OrdendecompraSIESA;

/**
 * This is the model class for table "ordendecompra".
 *
 * @property int $id
 * @property int|null $idCO
 * @property int|null $idTipoDocumento
 * @property float|null $consecutivo
 * @property string|null $fecha
 * @property int|null $idProveedor
 * @property int|null $idEstado
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Centrooperacion $CO
 * @property Proveedor $Proveedor
 * @property Tipodocumento $TipoDocumento
 */
class Ordendecompra extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordendecompra';
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
            [['idCO', 'idTipoDocumento', 'idProveedor', 'idEstado', 'created_by', 'updated_by',
            'nroPaquetes'], 'integer'],
            [['consecutivo', 'nitcomprador'], 'number'],
            [['fecha', 'created_at', 'updated_at', 'fechaEntrega', 'comprador', 'sucursalProveedor'], 'safe'],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
            [['idCO'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCO' => 'id']],
            [['idProveedor'], 'exist', 'skipOnError' => true, 'targetClass' => Proveedor::class, 'targetAttribute' => ['idProveedor' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idCO' => 'CO',
            'idTipoDocumento' => 'Tipo Documento',
            'consecutivo' => 'Consecutivo',
            'fecha' => 'Fecha',
            'idProveedor' => 'Proveedor',
            'idEstado' => 'Estado',
            'totalCantidadPedida' => 'Cantidad Pedida',
            'totalCantidadEntrada' => 'Cantidad Entrada',
            'totalCantidadPendiente' => 'Cantidad Pendiente',
            'nroPaquetes' => 'Nro Paquetes',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'fechaEntrega' => 'Fecha Entrega',
        ];
    }

    /**
     * Gets query for [[CO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCO()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCO']);
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

        /**
     * Gets query for [[Estadoordencompra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstadoordencompra()
    {
        return $this->hasOne(Estadoordencompra::class, ['id' => 'idEstado']);
    }


    /**
     * Gets query for [[TipoDocumento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoDocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    public function getTipoDocumentoentrada()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumentoEntrada']);
    }

    public function getPurchaseOrderItems()
    {
        return $this->hasMany(Ordendecompradetalle::className(), ['idOrdenCompra' => 'id']);
    }

    public function validarItemsOC (){

        $totalconitem = $this->getPurchaseOrderItems()->andWhere(['IS NOT', 'idItem', null])->sum('cantidadPendiente');
        $totalOC = $this->getPurchaseOrderItems()->sum('cantidadPendiente');

        if ($totalconitem != $totalOC){
            return false;
        }

        return true;

    }

    public function getValidacionItemsOC()
    {
        if ($this->validarItemsOC()) {
            return 'Sí'; // O cualquier otro texto que desees mostrar
        } else {
            return 'No'; // O cualquier otro texto que desees mostrar
        }
    }

    public static function insertarDatosOC($idCia, $idco, $idtipodocumento, $consecutivo){

        $contador = 0;
        $model = Centrooperacion::findOne(['id' => $idco]);
        $codigoCO = $model->codigo;

        $model = Tipodocumento::findOne(['id' => $idtipodocumento]);
        $codigoTipoDocumento = $model->codigo;

        $datos = OrdendecompraSIESA::obtenerDatosPorConsecutivo ($idCia, 
                                                        $codigoCO, 
                                                        $codigoTipoDocumento, 
                                                        $consecutivo);

        foreach ($datos as $fila) {
            $modelCO = new Centrooperacion ();
            $modelCO->codigo = $fila['idCO'];
            $modelCO->nombre = $fila['CO'];
            $idCO = Centrooperacion::actualizarRegistro($modelCO);
        
            $modelTD = new Tipodocumento();
            $modelTD->codigo = $fila['idTipoDocumento'];
            $modelTD->nombre = $fila['tipoDocumento'];
            $idTipoDocumento = Tipodocumento::actualizarRegistro($modelTD);

            $consecutivo = $fila['consecutivo'];

            $idProveedor = Proveedor::actualizarRegistroSIESA($fila);

            $model = Ordendecompra::findOne(['idCO' => $idCO,
                                                'idTipoDocumento' => $idTipoDocumento,
                                                'consecutivo' => $consecutivo
                                            ]);
            if ($model == null){
                $model = new Ordendecompra();
                $model->idCO = $idCO;
                $model->idTipoDocumento = $idTipoDocumento;
                $model->consecutivo =$consecutivo;
            }

            $modelEO = new Estadoordencompra();
            $modelEO->nombre = $fila['estadoDcto'];
            $idEstado = Estadoordencompra::actualizarRegistro($modelEO);

            $model->fecha = $fila['fecha'];
            $model->idProveedor = $idProveedor;
            $model->idEstado = $idEstado;

            $model->comprador = $fila['comprador'];
            $model->nitcomprador = $fila['nitcomprador'];
            $model->sucursalProveedor = $fila['sucursal'];

            if (!$model->save()){
                continue;
            }

            $idCia = $fila['idCia'];
            $id = $fila['id'];
            $detalle = OrdendecompraSIESA::obtenerDatosDetalleOC($idCia, $id);

            $registrosInsertados = 0;
            if (!empty($detalle)) {
                // Inserta los datos en el modelo 2
                //var_dump($detalle); die("hola");
                $registrosInsertados = Ordendecompradetalle::insertarDetalleOC($model->id, $detalle);
            }

            if ($registrosInsertados <= 0){
                $contador = $registrosInsertados;
                $numRegistrosBorrados = Ordendecompradetalle::deleteAll(['idOrdenCompra' => $model->id]);
                $numRegistrosBorrados = Ordendecompra::deleteAll(['id' => $model->id]);
                break;
            }

            $contador++;
        }

        return $contador;

    }
}
