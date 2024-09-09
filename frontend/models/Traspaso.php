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
            [['idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'idUltimoItem', 'created_by', 'updated_by'], 'integer'],
            [['consecutivo', 'und_traspaso', 'und_empaque'], 'number'],
            [['serie',], 'string', 'max' => 5],
            [['updated_at', 'created_at', 'fechaDesde', 'fechaHasta',], 'safe'],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
            [['idBodegaOrigen'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaOrigen' => 'id']],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
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
}
