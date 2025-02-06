<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "programacionentregamercancia".
 *
 * @property int $id
 * @property int $idAgendaEntregaMercancia
 * @property int $idEmpleadoLogistica
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Agendaentregamercancia $agendaEntregaMercancia
 */
class Programacionentregamercancia extends \yii\db\ActiveRecord
{
    public $fechaDesde;
    public $fechaHasta;
    public $idAgenda;
    public $codigoCentroOperacion;
    public $codigoTipoDocumento;
    public $numeroOrdenCompra;
    public $fechaCita;
    public $nombreCategoria;
    public $unidades;
    public $unidadesCumplidas;
    public $nombreUsuario;
    public $unidadesEmpaque;
    public $articulo;
    public $idProgramacion;
    public $nombreEstadoConteo;
    public $nombreEstadoProgramacion;
    public $idEstadoConteo;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'programacionentregamercancia';
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
            [['idAgendaEntregaMercancia', 'item'], 'required','message' => '{attribute} Es Un Valor Obligatorio'],
            [['idAgendaEntregaMercancia', 'idEmpleadoLogistica', 'idEstado', 'created_by', 
            'updated_by', 'idUserConteo', 'unidadxPaquete', 'puedeModificarEntrada'], 'integer'],
            [['created_at', 'updated_at', 'unidadesAsignadas'], 'safe'],
            [['idAgendaEntregaMercancia', 'item', 'idUserConteo'], 'unique', 'targetAttribute' => ['idAgendaEntregaMercancia', 'item', 'idUserConteo'], 'message' => 'El Item Ya Esta Asignado al Usuario.'],
            [['idAgendaEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Agendaentregamercancia::class, 'targetAttribute' => ['idAgendaEntregaMercancia' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idAgendaEntregaMercancia' => 'Radicado',
            'item' => 'Item',
            'idEmpleadoLogistica' => 'Empleado',
            'idUserConteo' => 'Usuario Conteo',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'unidadesAsignadas' => 'Unidades',
            'unidadxPaquete' => 'UND X PAQ',
            'puedeModificarEntrada' => 'Ingreso Manual UND',
            'nombreEstadoProgramacion' => 'Estado Conteo'
        ];
    }

    /**
     * Gets query for [[AgendaEntregaMercancia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendaEntregaMercancia()
    {
        return $this->hasOne(Agendaentregamercancia::class, ['id' => 'idAgendaEntregaMercancia']);
    }

    public function getUnidadEmpaque()
    {
        return $this->hasOne(unidadempaque::class, ['id' => 'unidadxPaquete']);
    }

    public function getEmpleadoLogistica()
    {
        return $this->hasOne(Empleadologistica::class, ['id' => 'idEmpleadoLogistica']);
    }

    public function getEstado()
    {
        return $this->hasOne(Estadoprogramacion::class, ['id' => 'idEstado']);
    }

    public function getUserConteo()
    {
        return $this->hasOne(Userconteo::class, ['id' => 'idUserConteo']);
    }

    public static function registrarItemsOC ($idagenda, $idordencompra, $idcategoria, $idfactura = null){

        $codigo = 0;
        $modelestado = Estadoprogramacion::findOne(['codigo' => $codigo]);

        $listaitems = Ordendecompradetalle::listaReferenciasOrdenCompra ($idordencompra, $idcategoria);

        foreach($listaitems as $item){
            $model = Programacionentregamercancia::findOne([
                                                    'idAgendaEntregaMercancia' => $idagenda,
                                                    'idFacturaEntregaMercancia' => $idfactura,
                                                    'item' => $item['item']
                                        ]);
            if ($model == null){
                $model = new Programacionentregamercancia ();

                $model->idAgendaEntregaMercancia = $idagenda;
                $model->idFacturaEntregaMercancia = $idfactura;
                $model->item = $item['item'];
                $model->referencia = $item['referencia'];
                $model->descripcion = $item['descripcion'];
                $model->idEstado = $modelestado->id;
                $model->unidadesAsignadas = $item['cantidad'];

                if(!$model->save()){
                    var_dump($model->getErrors()); die("hola");
                }

                /*
                if($model->save()){
                    $idprogramacionentregamercancia  = $model->id;
                    $ok = Conteoentregamercancia::grabarItemParaConteo ($idordencompra, 
                                                                        $idprogramacionentregamercancia, 
                                                                        $item['item']);   
                            
                }else{
                    var_dump($model->getErrors()); die("hola");
                }*/

            }
        }
    }

    public static function asignarUserConteo ($idagenda, $iduser, $idempleadologistica, $idfactura = null)
    {
        $numRegistrosActualizados = 0;
        $modelestado = Estadoprogramacion::findOne(['codigo' => 1]);

        $models = Programacionentregamercancia::find()
                                ->where([
                                                        'idAgendaEntregaMercancia' => $idagenda, 
                                                        'idFacturaEntregaMercancia' => $idfactura
                                                    ])
                                ->andWhere(['OR', ['idUserConteo' => null], ['idUserConteo' => '']])
                                ->all();

        foreach ($models as $model) {
            $model->scenario = 'scenarioUser';
            $model->idUserConteo = $iduser;
            $model->idEmpleadoLogistica = $idempleadologistica;
            $model->idEstado = $modelestado->id;

            $model->save(false); // El parámetro false evita que se realicen las validaciones
            $numRegistrosActualizados = $numRegistrosActualizados + 1;
        }

        if ($numRegistrosActualizados > 0){
            $modelagendaentrega = Agendaentregamercancia::findOne(['id' => $idagenda]);

            $codigo = 1;
            $modelestado = Estadoconteo::findOne(['codigo' => $codigo]);

            $modelagendaentrega->idEstadoConteo = $modelestado->id;
            $modelagendaentrega->save();
        }

        return $numRegistrosActualizados;
    }

    public static function totalUnidadesAsignadas ($idagenda, $item = null){

        /*$total = Programacionentregamercancia::find()
                        ->select(['SUM(unidadesAsignadas) AS total'])
                        ->where([
                                'idAgendaEntregaMercancia' => $model->idAgendaEntregaMercancia,
                        ])->scalar();*/

        $total = Programacionentregamercancia::find()
                                            ->where(['idAgendaEntregaMercancia' => $idagenda])
                                            //->andWhere('idUserConteo IS NOT NULL')
                                            ->andFilterWhere(['item' => $item])
                                            ->sum('unidadesAsignadas');                                            
        return $total;
    }

    public static function countProgramacionByAgendaAndEstado($idAgenda, $idEstado)
    {
        return self::find()
            ->andFilterWhere(['idAgendaEntregaMercancia' => $idAgenda])
            ->andFilterWhere(['idEstado' => $idEstado])
            ->count();
    }

}


