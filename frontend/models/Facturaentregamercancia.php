<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "facturaentregamercancia".
 *
 * @property int $id
 * @property int $idAgendaEntregaMercancia
 * @property int|null $idProgramacionEntregaMercancia
 * @property string $numeroFactura
 * @property string|null $observaciones
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Agendaentregamercancia $idAgendaEntregaMercancia0
 * @property Programacionentregamercancia $idProgramacionEntregaMercancia0
 * @property Programacionentregamercancia[] $programacionentregamercancias
 */
class Facturaentregamercancia extends \yii\db\ActiveRecord
{
    public $radicado;
    public $idCentroOperacion;
    public $almacen;
    public $idTipoDocumento;
    public $serie;
    public $consecutivo;
    public $nit;
    public $razonSocial;
    public $categoria;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'facturaentregamercancia';
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
            [['idAgendaEntregaMercancia', 'numeroFactura'], 'required','message' => '{attribute} Es Un Valor Obligatorio'],
            [['idAgendaEntregaMercancia', 'idProgramacionEntregaMercancia', 'created_by', 
            'updated_by', 'idTipoDocumento', 'idCentroOperacion', 'consecutivo', 
            'idTipoDocumentoEntrada', 'consignacion', 'consecutivoDocumentoEntrada', 
            'idCODocumentoEntrada'], 'integer'],
            [['created_at', 'updated_at', 'numeroFacturaLegaliza', 'fechaDocumentoEntrada'], 'safe'],
            [['numeroFactura'], 'string', 'max' => 20],
            [['observaciones'], 'string', 'max' => 250],
            [['idAgendaEntregaMercancia', 'numeroFactura'], 'unique', 'targetAttribute' => ['idAgendaEntregaMercancia', 'numeroFactura'],'message' => '{attribute} Número de Factura YA Existe'],
            [['idAgendaEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Agendaentregamercancia::class, 'targetAttribute' => ['idAgendaEntregaMercancia' => 'id']],
            [['idProgramacionEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Programacionentregamercancia::class, 'targetAttribute' => ['idProgramacionEntregaMercancia' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idAgendaEntregaMercancia' => 'Id Agenda Entrega Mercancia',
            'idProgramacionEntregaMercancia' => 'Id Programacion Entrega Mercancia',
            'numeroFactura' => 'Número Factura',
            'observaciones' => 'Observaciones',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'razonSocial' => 'Razón Social',
            'consecutivo' => 'Número Orden',
            'almacen' => 'Almacén',
            'idCentroOperacion' => 'Almacén',
            'idTipoDocumento' => 'Serie'
        ];
    }

    /**
     * Gets query for [[IdAgendaEntregaMercancia0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdAgendaEntregaMercancia0()
    {
        return $this->hasOne(Agendaentregamercancia::class, ['id' => 'idAgendaEntregaMercancia']);
    }

    /**
     * Gets query for [[IdProgramacionEntregaMercancia0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdProgramacionEntregaMercancia0()
    {
        return $this->hasOne(Programacionentregamercancia::class, ['id' => 'idProgramacionEntregaMercancia']);
    }

    /**
     * Gets query for [[Programacionentregamercancias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionentregamercancias()
    {
        return $this->hasMany(Programacionentregamercancia::class, ['idFacturaEntregaMercancia' => 'id']);
    }

    public function getTipoDocumentoentrada()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumentoEntrada']);
    }
}
