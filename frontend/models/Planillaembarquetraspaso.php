<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "planillaembarquetraspaso".
 *
 * @property int $id
 * @property int $idPlanillaEmbarque
 * @property int $idTraspaso
 * @property int $idBodegaOrigen
 * @property int $idBodegaDestino
 * @property int $unidades
 * @property int $unidadesEmp
 * @property float $sello
 * @property string $fechaRecibido
 * @property int $idUsuarioRecibido
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $idBodegaDestino0
 * @property Bodegas $idBodegaOrigen0
 * @property Planillaembarque $idPlanillaEmbarque0
 * @property Traspaso $idTraspaso0
 */
class Planillaembarquetraspaso extends \yii\db\ActiveRecord
{

    public $codAlmacenOrigen;
    public $almacenOrigen;
    public $codAlmacenDestino;
    public $almacenDestino;
    public $tipoDocumento;
    public $consecutivoDocumento;
    public $consecutivoInterno;
    public $tipoDocumentoInterno;
    public $fechaPlanillaembarque;
    public $horaPlanillaembarque;
    public $estado;
    public $usuarioRecibido;
    public $fechaTraspaso;
    public $horaTraspaso;
    public $usuarioCreador;
    public $usuarioPlanilla;
    public $selloLlegada;
    public $selloSalida;
    public $conductor;
    public $orden;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planillaembarquetraspaso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'sello', 'fechaRecibido', 'idUsuarioRecibido', 'idEstado', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'idUsuarioRecibido', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['sello'], 'number'],
            [['fechaRecibido', 'created_at', 'updated_at'], 'safe'],
            [['idPlanillaEmbarque'], 'exist', 'skipOnError' => true, 'targetClass' => Planillaembarque::class, 'targetAttribute' => ['idPlanillaEmbarque' => 'id']],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            [['idBodegaOrigen'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaOrigen' => 'id']],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idPlanillaEmbarque' => 'Numero Planilla',
            'idTraspaso' => 'Id Traspaso',
            'idBodegaOrigen' => 'Id Bodega Origen',
            'idBodegaDestino' => 'Id Bodega Destino',
            'unidades' => 'Unidades',
            'unidadesEmp' => 'Unidades Emp',
            'sello' => 'Sello',
            'fechaRecibido' => 'Fecha Recibido',
            'idUsuarioRecibido' => 'Id Usuario Recibido',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',

            'codAlmacenOrigen' => 'Cod. Almacén Origen',
            'almacenOrigen' => 'Almacén Origen',
            'codAlmacenDestino' => 'Cod. Almacén Destino',
            'almacenDestino' => 'Almacén Destino',
            'tipoDocumento' => 'Almacén tipoDocumento',
            'consecutivoDocumento' => 'numero',
            'fechaPlanillaembarque' => 'Fec.Des',
            'horaPlanillaembarque' => 'Hor.Des',
            'estado' => 'Estado',
            'usuarioRecibido' => 'Usu.Rec',
            'fechaTraspaso' => 'Fecha',
            'horaTraspaso' => 'Hora',
            'orden' => 'Orden llegada'
        ];
    }
    // Fec.Rec  : fecha en la cuan se recibe el documento en las tiendas después de haberse enviado con planilla de embarque 
    // Hor.Rec  : hora en la cual es recibido el documento por parte de la tienda
    // Usu.Rec  : nombre del usuario de la tienda que recibe el documento luego de recibirlo físicamente y con planilla de embarque 
    // Planilla    : numero de la planilla     (210-xxxx)    la primera cifra indica el sector de la planilla (210) bodega principal,  los números siguientes es el consegutivo usado para cada planilla 
    // Fecha recibido transito : es para los documentos TRT o documentos elaborados para traslados entre tiendas ,este sector es para el recibo de estos documentos en transito CEDI , seguido a este se cambia a despachado CEDI ,asi se identifican los docuemtos elaborados para nivelacion de producto entre tiendas .
    // Hora recibido transito  : indica la hora en que los documentos en trasito fueron recibidos en el CEDI por el auxiliar administrativo de transporte. 
    // Planilla transito     : planilla que se elabora para el envío nuevamente desde el CEDI de los documentos TRT o traslados entre tiendas recibidos en trasito CEDI

    /**
     * Gets query for [[IdBodegaDestino0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodegaDestino()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaDestino']);
    }

    /**
     * Gets query for [[IdBodegaOrigen0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodegaOrigen()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaOrigen']);
    }

    /**
     * Gets query for [[IdPlanillaEmbarque0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanillaEmbarque()
    {
        return $this->hasOne(Planillaembarque::class, ['id' => 'idPlanillaEmbarque']);
    }

    /**
     * Gets query for [[IdTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }
    
}
