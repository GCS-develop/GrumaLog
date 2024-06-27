<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspaso".
 *
 * @property int $id
 * @property int|null $idCentroOperacion
 * @property int $idBodegaOrigen
 * @property int $idBodegaDestino
 * @property int $numeroCajas
 * @property int|null $idTipoDocumento
 * @property float|null $consecutivo
 * @property int|null $idEstado
 * @property int|null $idUltimoItem
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Bodegas $bodegaDestino
 * @property Bodegas $bodegaOrigen
 * @property Centrooperacion $centroOperacion
 * @property Estadotraspaso $estado
 * @property Tipodocumento $tipoDocumento
 * @property Traspasodetalle[] $traspasodetalles
 */
class Traspaso extends \yii\db\ActiveRecord
{
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
            [['idCentroOperacion', 'idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'idUltimoItem', 'created_by', 'updated_by'], 'integer'],
            [['idBodegaOrigen', 'idBodegaDestino'], 'required'],
            [['consecutivo'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
            [['idBodegaOrigen'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaOrigen' => 'id']],
            [['idCentroOperacion'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCentroOperacion' => 'id']],
            [['idEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estadotraspaso::class, 'targetAttribute' => ['idEstado' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idCentroOperacion' => 'Id Centro Operacion',
            'idBodegaOrigen' => 'Id Bodega Origen',
            'idBodegaDestino' => 'Id Bodega Destino',
            'numeroCajas' => 'Numero Cajas',
            'idTipoDocumento' => 'Id Tipo Documento',
            'consecutivo' => 'Consecutivo',
            'idEstado' => 'Id Estado',
            'idUltimoItem' => 'Id Ultimo Item',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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
     * Gets query for [[CentroOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentroOperacion()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCentroOperacion']);
    }

    /**
     * Gets query for [[Estado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstado()
    {
        return $this->hasOne(Estadotraspaso::class, ['id' => 'idEstado']);
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

    /**
     * Gets query for [[Traspasodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspasodetalles()
    {
        return $this->hasMany(Traspasodetalle::class, ['idTraspaso' => 'id']);
    }

    public static function traspasoERP ($model){

        $modeltpodcto = new TiposDocumentoWs();
        $tiposdctos = $modeltpodcto->getAllTiposDocumentoWs();

        $consecutivo_documento = 0;
        foreach ($tiposdctos as $tipodcto) {
            if ($tipodcto['Id_tipodocto'] == $model->tipoDocumento->codigo){
                $consecutivo_documento = $tipodcto['Consecutivo_Proximo']; 
                break;
            }
        }

        $json = null;

        if ($consecutivo_documento > 0){

            $modelDetalles = $model->traspasodetalles;

            $items = [];
            $nroregistro = 1;
            foreach ($modelDetalles as $detalle) {
                $items[] = [
                    'f350_id_co' => trim($model->centroOperacion->codigo),
                    'f350_id_tipo_docto' => trim($model->tipoDocumento->codigo),
                    'f350_consec_docto' => $consecutivo_documento,
                    'f350_fecha' => date('Ymd', strtotime($model->created_at)),
                    'f350_id_tercero' => '',
                    'f350_notas' => '',
                    'f450_id_bodega_salida' => trim($model->bodegaDestino->codigo),
                    'f450_id_bodega_entrada' => trim($model->bodegaOrigen->codigo),
                    'f470_id_co' => trim($model->centroOperacion->codigo),
                    'f470_id_tipo_docto' => trim($model->tipoDocumento->codigo),
                    'f470_consec_docto' => $consecutivo_documento,
                    'f470_nro_registro' => $nroregistro,
                    'f470_id_bodega' => trim($model->bodegaDestino->codigo),
                    'f470_id_motivo' => '',
                    'f470_id_co_movto' => trim($model->centroOperacion->codigo),
                    'f470_id_unidad_medida' => trim($detalle->item->unidadOrden),
                    'f470_cant_base' => sprintf('%015.4f', trim($detalle->cantidad)),
                    'f470_costo_prom_uni' => '000000000000000.0000',
                    'f470_notas' => '',
                    'f470_id_item' => $detalle->item->item,
                    'f470_id_ext1_detalle' => trim($detalle->item->talla->nombre),
                    'f470_id_ext2_detalle' => trim($detalle->item->color->nombre),
                    'f470_id_un_movto' => '',
                ];

                $nroregistro = $nroregistro + 1;
            }

            $json = Json::encode(['Documentos' => $items]);
        }

        return $json;
    }
}
