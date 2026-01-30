<?php

namespace frontend\models;

use common\models\User;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use frontend\models\Estadodespacho;
use frontend\models\Transportadora;
use frontend\models\Vehiculo;
use frontend\models\Conductor;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "planillaembarque".
 *
 * @property int $id
 * @property string $fechaDespacho
 * @property string $horaDespacho
 * @property int|null $idTransportadora
 * @property int|null $idVehiculo
 * @property string|null $placa
 * @property int|null $idConductor
 * @property string $nombreConductor
 * @property string|null $sello
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * 
 *   @property Estadodespacho $idEstado
 *   @property Transportadora $idTransportadora
 *   @property Vehiculo $idVehiculo
 *   @property Conductor $idConductor
 */


class Planillaembarque extends \yii\db\ActiveRecord
{

    public $fechaDesde;
    public $fechaHasta;
    public $numeroDocumento;
    public $numeroDocumentoInterno;

    public $Codigobodegaorigen;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planillaembarque';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'fechaDespacho',
                    'horaDespacho',
                    'idTransportadora',
                    'flotaPropia'
                ],
                'required',
                'message' => '{attribute} Es Un Valor Obligatorio'
            ],
            [
                [
                    'fechaDespacho',
                    'Codigobodegaorigen',
                    'created_at',
                    'updated_at',
                    'flotaPropia',
                    'placa',
                    'nombreConductor'
                ],
                'safe'
            ],
            [['idTransportadora', 'idVehiculo', 'idConductor', 'created_by', 'updated_by', 'idEstado'], 'integer'],
            [['horaDespacho'], 'string', 'max' => 10],
            [['placa'], 'string', 'max' => 20],
            [['nombreConductor'], 'string', 'max' => 150],
            [['sello'], 'string', 'max' => 50],
            [['sello'], 'unique'],
        ];
    }

    public function validarConductor($attribute, $params)
    {
        //var_dump($this->idConductor . ' - ' . $this->nombreConductor); die("hola");

        if (empty($this->idConductor) && empty($this->nombreConductor)) {
            $this->addError($attribute, 'Falta Especificar Datos Conductor.');
        }
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
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'fechaDespacho' => 'Fecha',
            'horaDespacho' => 'Hora',
            'idTransportadora' => 'Transportadora',
            'idVehiculo' => 'Vehículo',
            'placa' => 'Placa',
            'idConductor' => 'Conductor',
            'nombreConductor' => 'Nombre Conductor',
            'Codigobodegaorigen' => 'Codigo Bodega Origen',
            'sello' => 'Sello',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'flotaPropia' => 'Vehículo Empresa',
            'idEstado' => 'Estado'
        ];
    }

    public function getTransportadora()
    {
        return $this->hasOne(Transportadora::class, ['id' => 'idTransportadora']);

    }

    public function getCodigoBodegaOrigen()
    {
        return $this->hasOne(Bodegas::class, ['codigo' => 'Codigobodegaorigen']);
    }

    public function getVehiculo()
    {
        return $this->hasOne(Vehiculo::class, ['id' => 'idVehiculo']);
    }

    public function getConductor()
    {
        return $this->hasOne(Conductor::class, ['id' => 'idConductor']);
    }

    public function getPlanillaembarquetraspaso()
    {
        return $this->hasMany(Planillaembarquetraspaso::class, ['idPlanillaEmbarque' => 'id']);
    }

    public function getPlanillaembarquetraspasoanulado()
    {
        return $this->hasOne(Planillaembarquetraspaso::class, ['idPlanillaEmbarque' => 'id'])
            ->andWhere(['idEstado' => '5']);
    }

    public function getAnularplanillatraspaso()
    {
        // Realiza la actualización masiva utilizando updateAll
        return Planillaembarquetraspaso::updateAll(
            ['idEstado' => 5], // Campos a actualizar
            ['idPlanillaEmbarque' => $this->id] // Condiciones
        );
    }

    public function getTotalUnidades()
    {
        // Obtiene la relación de Conteoentregamercancias y suma la cantidad de cada uno
        return $this->getPlanillaembarquetraspaso()->sum('unidades');
    }

    public function getTotalUnidadesEmp()
    {
        // Obtiene la relación de Conteoentregamercancias y suma la cantidad de cada uno
        return $this->getPlanillaembarquetraspaso()->sum('unidadesEmp');
    }
    public function getEstado()
    {
        return $this->hasOne(Estadodespacho::class, ['id' => 'idEstado']);
    }

    public function getUsuario()
    {
        return $this->hasOne(user::class, ['id' => 'created_by']);
    }
    public function getPlanillaembarquebodega($idBodegaDestino = 0)
    {
        return $this->hasOne(Planillaembarquebodega::class, ['idPlanillaEmbarque' => 'id'])
            ->andOnCondition(['idBodegaDestino' => $idBodegaDestino]);
    }
    public function getListabodegasdestino()
    {
        return $this->hasMany(Planillaembarquetraspaso::class, ['idPlanillaEmbarque' => 'id'])
            ->joinWith('bodegaDestino')
            ->distinct()  // Aplica distinct a los resultados de la consulta
            ->select(['idBodegaDestino', 'bodegas.nombre AS bodega_nombre'])
            ->orderBy('idBodegaDestino');
    }

}
