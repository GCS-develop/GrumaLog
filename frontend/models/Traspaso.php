<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use common\models\User;
use yii\db\Query;

use common\models\OrdendecompraSIESA;


/**
 * This is the model class for table "traspaso".
 *
 * @property int $id
 * @property int $idBodegaOrigen
 * @property int $idBodegaDestino
 * @property int $numeroCajas
 * @property int|null $idTipoDocumento
 * @property float|null $consecutivo
 * @property string|null $serie
 * @property int|null $und_traspaso
 * @property int|null $und_empaque
  * @property int|null $tipoMovimiento

 * @property tipoDocumento $tipoDocumento
 * @property Bodegas $bodegaDestino
 * @property Bodegas $bodegaOrigen
 * @property Traspasodetalle[] $traspasodetalles
 */
class Traspaso extends \yii\db\ActiveRecord
{
    public $serie;
    public $und_empaque;
    public $und_traspaso;
    public $impresora;
    public $fechaDesde;
    public $fechaHasta;
    public $estadoPlanilla;
    public $consecutivosiesa;
    public $idusertraspasocdsc;
    public $fechaRecibido;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspaso';
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
            [['idBodegaOrigen', 'idBodegaDestino'], 'required'],
            [['idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'idUltimoItem', 'created_by', 'updated_by', 'tipoMovimiento'], 'integer'],
            [['consecutivo', 'und_traspaso', 'und_empaque'], 'number'],
            [['serie',], 'string', 'max' => 5],
            [['updated_at', 'created_at', 'fechaDesde', 'fechaHasta', 'anula_at', 'anula_by', 'muelle_at', 'muelle_by', 'idusertraspasocdsc'], 'safe'],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
            [['idBodegaOrigen'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaOrigen' => 'id']],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
            ['transferenciaerp', 'in', 'range' => [0, 1], 'message' => 'El valor debe ser 1 (Enviada) o 0 (No enviada).'],

        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idBodegaOrigen' => 'Bodega Origen',
            'idBodegaDestino' => 'Bodega Destino',
            'numeroCajas' => 'Cajas',
            'serie' => 'serie',
            'consecutivo' => 'Consecutivo',
            'idEstado' => 'Estado',
            'created_by' => 'usuario',
            'updated_by' => 'updated_by',
            'updated_at' => 'Fecha',
            'und_traspaso' => 'Und.Traspaso',
            'und_empaque' => 'Und.Empaque',
            'codeBodegaDestino' => 'codigo bodega destino',
            'codeBodegaOrigen' => 'codigo bodega origen',
            'caja' => 'Caja',
            'horaInicio' => 'hora inicio',
            'fechaUltimoRegistro' => 'Fecha ultimo registro',
            'horaUltimoRegistro' => 'Hora ultimo registro',
            'impresora' => 'impresora',
            'tipoMovimiento' => 'Tipo de movimiento',
            'muelle_at' => 'Fecha en muelle',
            'muelle_by' => 'Usuario en muelle',
            'idusertraspasocdsc' => 'Usuario',
            'transferenciaerp' => 'Transferencia'

        ];
    }

    /**
     * Gets query for [[BodegaDestino]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodegaDestino()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaDestino']);
    }

    /**
     * Gets query for [[BodegaOrigen]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodegaOrigen()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaOrigen']);
    }
    public function getEstadoTransferencia()
    {
        return $this->transferenciaerp == 1 ? 'Enviado' : 'Sin Enviar';
    }
    
    /**
     * Gets query for [[Traspasodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspasodetalles()
    {
        return $this->hasMany(Traspasodetalle::class, ['idTraspaso' => 'id']);
    }
    public function getTraspasodetalle()
    {
        return $this->hasOne(Traspasodetalle::class, ['idTraspaso' => 'id']);
    }
    public function getEstado()
    {
        return $this->hasOne(Estadotraspaso::class, ['id' => 'idEstado']);
    }
    public function getUsuario()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::className(), ['id' => 'idTipoDocumento']);
    }

    public function getCodigoerp()
    {
        return $this->hasOne(Documentosiesa::class, ['idGruma' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getUltimaplanillaembarquetraspaso()
    {
        return $this->hasOne(Planillaembarquetraspaso::class, ['idTraspaso' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }

    public function enviarTraspasosPorPost()
    {
        // Obtener todos los traspasos
        $traspasos = Traspaso::find()->all();

        // Convertir los traspasos en un array de datos
        $data = [];
        foreach ($traspasos as $traspaso) {
            $data[] = [
                'id' => $traspaso->id,
                'idBodegaOrigen' => $traspaso->idBodegaOrigen,
                'idBodegaDestino' => $traspaso->idBodegaDestino,
                'numeroCajas' => $traspaso->numeroCajas,
                'idTipoDocumento' => $traspaso->idTipoDocumento,
                'consecutivo' => $traspaso->consecutivo,
                'serie' => $traspaso->serie,
                'und_traspaso' => $traspaso->und_traspaso,
                'und_empaque' => $traspaso->und_empaque,
                // Añade aquí más atributos si es necesario
            ];
        }

        // Convertir el array de datos a JSON
        $jsonData = Json::encode($data);

        // Crear un cliente HTTP
        $httpClient = new Client();

        // Realizar la solicitud POST al servidor
        $response = $httpClient->createRequest()
            ->setMethod('post')
            ->setUrl('URL_DEL_SERVIDOR_DESTINO')
            ->setData($jsonData)
            ->send();

        // Verificar si la solicitud fue exitosa
        if ($response->isOk) {
            echo 'Los traspasos se enviaron correctamente.';
        } else {
            echo 'Hubo un error al enviar los traspasos.';
        }
    }


    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for the user who last updated the record.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }
    public function getAnulaByUser()
    {
        return $this->hasOne(User::class, ['id' => 'anula_by']);
    }
    public function getMuelleByUser()
    {
        return $this->hasOne(User::class, ['id' => 'muelle_by']);
    }
    public function getAllRecords()
    {
        return $this->getTraspasodetalles()->sum('cantidad');
    }

    public function getTotalRegistrosPaquetes()
    {
        return (float) (new Query())
            ->select(['SUM(td.cantidad)'])
            ->from('traspasodetalle td')
            ->innerJoin('item i', 'td.idItem = i.id')
            ->where(['td.idTraspaso' => $this->id])
            ->andWhere(['IS NOT', 'i.unidadEmpaque', null])
            ->scalar();
    }


    public function getTotalCantidadConUnidadEmpaqueNotNull()
    {
        //Retorna la cantidad en unidades de los que son paquetes, es decir si una lista tiene 10 unidades 
        // y 2 paquetes x2 entonces retorna 4  
        return (float) (new \yii\db\Query())
            ->select(['SUM(td.cantidad * COALESCE(ue.equivalencia, 1))'])
            ->from('traspasodetalle td')
            ->innerJoin('item i', 'td.idItem = i.id')
            ->leftJoin('unidadempaque ue', 'i.unidadEmpaque = ue.codigo')
            ->where(['td.idTraspaso' => $this->id])
            ->andWhere(['IS NOT', 'i.unidadEmpaque', null])
            ->scalar();

    }

    public function getTotalUnidades()
    {

        return (float) (new \yii\db\Query())
            ->select(['SUM(td.cantidad * COALESCE(ue.equivalencia, 1))'])
            ->from('traspasodetalle td')
            ->innerJoin('item i', 'td.idItem = i.id')
            ->leftJoin('unidadempaque ue', 'i.unidadEmpaque = ue.codigo')
            ->where(['td.idTraspaso' => $this->id])
            ->scalar();

    }

    public static function generarTraspasoDesdeTransferencia($idtransferenciaerp, $tipomovimiento = null)
    {

        if ($tipomovimiento == null) {
            $tipomovimiento = 1;
        }

        $sql = "SELECT 1 AS idCentroOperacion, bs.id AS idBodegaOrigen, tte.bodegaSalidaDocumento, 
                    be.id AS idBodegaDestino, tte.bodegaEntradaDocumento,
                    1 AS numeroCajas, td.id AS idTipoDocumento, tte.idTransferenciaerp AS consecutivo,
                    1 AS idEstado, NULL AS idUltimoItem, 1 AS transferenciaerp, :tipomovimiento AS tipoMovimiento
                FROM transferenciatransitoexcel tte
                INNER JOIN bodegas bs ON tte.bodegaSalidaDocumento = bs.codigo
                INNER JOIN bodegas be ON tte.bodegaEntradaDocumento = be.codigo
                INNER JOIN tipodocumento td ON tte.tipoDocumento = td.codigo
                WHERE tte.idTransferenciaerp = :idTransferenciaErp
                GROUP BY tte.idTransferenciaerp, bs.id, tte.bodegaSalidaDocumento, be.id, tte.bodegaEntradaDocumento, td.id";

        $command = Yii::$app->db->createCommand($sql);
        $command->bindValues([
            ':idTransferenciaErp' => $idtransferenciaerp,
            ':tipomovimiento' => $tipomovimiento
        ]);

        //echo $command->getRawSql();die("hola");

        $resultados = $command->queryAll();
        $id = null;

        foreach ($resultados as $resultado) {

            $traspaso = new Traspaso();
            $traspaso->idCentroOperacion = $resultado['idCentroOperacion'];
            $traspaso->idBodegaOrigen = $resultado['idBodegaOrigen'];
            $traspaso->idBodegaDestino = $resultado['idBodegaDestino'];
            $traspaso->numeroCajas = $resultado['numeroCajas'];
            $traspaso->idTipoDocumento = $resultado['idTipoDocumento'];
            $traspaso->consecutivo = $resultado['consecutivo'];
            $traspaso->idEstado = $resultado['idEstado'];
            $traspaso->idUltimoItem = $resultado['idUltimoItem'];
            $traspaso->transferenciaerp = $resultado['transferenciaerp'];
            $traspaso->tipoMovimiento = $resultado['tipoMovimiento'];

            if ($traspaso->save()) {

                $id = $traspaso->id;
                $model = Traspaso::findOne(['id' => $id]);
                $model->consecutivo = $id;
                $model->save();

                $bodegasalida = $resultado['bodegaSalidaDocumento'];
                $bodegaentrada = $resultado['bodegaEntradaDocumento'];

                self::generarTraspasoDetalle($idtransferenciaerp, $traspaso->id, $bodegasalida, $bodegaentrada, $tipomovimiento);
            } else {
                //var_dump($traspaso->getErrors()); die("hola");
                break;
            }
        }

        return $id;
    }

    public static function generarTraspasoDetalle($idtransferenciaerp, $idtraspaso, $bodegasalida, $bodegaentrada, $tipomovimiento)
    {
        $sqlDetalle = "
              SELECT tte.id, tte.item, tte.color, tte.talla, tte.cantidadBase AS cantidad
                FROM transferenciatransitoexcel tte
                INNER JOIN bodegas bs ON tte.bodegaSalidaDocumento = bs.codigo
                INNER JOIN bodegas be ON tte.bodegaEntradaDocumento = be.codigo
                WHERE tte.idTransferenciaerp = :idtransferenciaerp AND tte.bodegaSalidaDocumento = :bodegasalida 
                and tte.bodegaEntradaDocumento = :bodegaentrada 
                ORDER BY tte.item 
        ";

        $commandDetalle = Yii::$app->db->createCommand($sqlDetalle);

        $commandDetalle
            ->bindValue(':idtransferenciaerp', $idtransferenciaerp)
            ->bindValue(':bodegasalida', $bodegasalida)
            ->bindValue(':bodegaentrada', $bodegaentrada);

        $detalles = $commandDetalle->queryAll();

        foreach ($detalles as $detalle) {

            $item = $detalle['item'];
            $color = $detalle['color'];
            $talla = $detalle['talla'];

            $iditem = Item::grabarDataDesdeSIESA($item, $color, $talla);
            $modelitem = Item::findOne(['id' => $iditem]);

            $traspasoDetalle = new Traspasodetalle();
            $traspasoDetalle->idTraspaso = $idtraspaso;
            $traspasoDetalle->idItem = $iditem;
            $traspasoDetalle->codigoitem = $modelitem->codigoBarras;

            $cantidadBase = $detalle['cantidad'];

            $equivalencia = $modelitem->unidadempaque ? $modelitem->unidadempaque->equivalencia : 1;
            $cantidad = $cantidadBase / $equivalencia;

            $traspasoDetalle->cantidad = $cantidad;
            $traspasoDetalle->cantidadTransferencia = $cantidadBase;

            if (!$traspasoDetalle->save()) {
                var_dump($traspasoDetalle->getErrors());
                die("hola");
            }

            switch ($tipomovimiento) {
                case 2:
                    $movimientomsg = 'Crossdocking certificado => ';
                    break;
                case 3:
                    $movimientomsg = 'Crossdocking CDSC => ';
                    break;
                default:
                    $movimientomsg = 'Traspaso => ';
                    break;
            }

            $transferencia = Transferenciatransitoexcel::findOne(['id' => $detalle['id']]);
            $numero = $transferencia->tipoDocumento . $idtraspaso;
            $transferencia->numero = $numero;
            $transferencia->notas = $movimientomsg . $numero;
            $transferencia->codigoBarras = $modelitem->codigoBarras;
            $transferencia->save();
        }
    }

    public static function sincronizarTraspaso($id)
    {

        $model = Traspaso::findOne(['id' => $id]);
        $tipodocumento = $model->tipodocumento->codigo;
        $idgruma = $id;
        $numerodocumento = $model->consecutivo;

        $tipomovimiento = 'No. Traspaso => ';
        if ($model->tipoMovimiento == 2) {
            $tipomovimiento = 'Crossdocking certificado => ';
        }

        $traspasoSiesa = OrdendecompraSIESA::obtenerDatosDocumento($tipodocumento, $numerodocumento, $tipomovimiento);
        Yii::trace('Buscar traspaso en siesa', __METHOD__);

        //var_dump($traspasoSiesa); die ("STOP");

        $guardoDatos = Documentosiesa::grabarDatos($traspasoSiesa, $idgruma);

    }

    public function getDocumentosiesa()
    {
        return $this->hasMany(Documentosiesa::class, ['idGruma' => 'id']);
    }

}
