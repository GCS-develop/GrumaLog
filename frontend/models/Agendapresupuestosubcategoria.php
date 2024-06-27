<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "agendapresupuestosubcategoria".
 *
 * @property int $id
 * @property int|null $periodoAnio
 * @property int|null $periodoMes
 * @property string|null $crossDocking
 * @property string|null $categoria
 * @property string|null $subcategoria
 * @property string|null $fechaLlegada
 * @property float|null $cantidad
 */
class Agendapresupuestosubcategoria extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendapresupuestosubcategoria';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idAgendaPresupuesto', 'categoria_id', 'subcategoria_id', 'crossdocking_id', 'cantidad', 'fechaLlegada'], 
                'required', 'message' => '{attribute} Es Un Valor Obligatorio'
            ],
            [['periodoAnio', 'periodoMes', 'categoria_id', 'subcategoria_id', 'crossdocking_id', 
            'idAgendaPresupuesto', 'periodoAnio', 'periodoMes'], 'integer'],
            [['fechaLlegada'], 'safe'],
            [['cantidadAgendada', 'cantidad'], 'number'],
            [['crossDocking', 'categoria', 'subcategoria'], 'string', 'max' => 50],
            [['idAgendaPresupuesto'], 'exist', 'skipOnError' => true, 'targetClass' => Agendapresupuesto::class, 'targetAttribute' => ['idAgendaPresupuesto' => 'id']],
            [['categoria'], 'exist', 'skipOnError' => true, 'targetClass' => Categoria::class, 'targetAttribute' => ['categoria' => 'nombre']],
            [['subcategoria'], 'exist', 'skipOnError' => true, 'targetClass' => Subcategoria::class, 'targetAttribute' => ['subcategoria' => 'nombre']],
            [['idAgendaPresupuesto', 'crossDocking', 'fechaLlegada', 'categoria_id', 'subcategoria_id'], 'unique', 'targetAttribute' => ['idAgendaPresupuesto', 'crossDocking', 'fechaLlegada', 'categoria_id', 'subcategoria_id'], 'message' => 'Este Registro YA Existe'],
        ];
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
            'idAgendaPresupuesto' => 'ID Agenda',
            'id' => 'ID',
            'periodoAnio' => 'Año',
            'periodoMes' => 'Mes',
            'crossDocking' => 'Destino',
            'categoria' => 'Categoría',
            'subcategoria' => 'Subcategoría',
            'fechaLlegada' => 'Fecha Llegada',
            'cantidad' => 'Cantidad',
            'categoria_id' => 'Categoría', 
            'subcategoria_id' => 'Subcategoría',
            'crossdocking_id' => 'CrossDocking'
        ];
    }

    /**
     * Gets query for [[AgendaPresupuesto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendaPresupuesto()
    {
        return $this->hasOne(Agendapresupuesto::class, ['id' => 'idAgendaPresupuesto']);
    }

    /**
     * Gets query for [[IdCategoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    public function getNombreCategoria()
    {
        return $this->hasOne(Categoria::class, ['nombre' => 'categoria']);
    }

    /**
     * Gets query for [[IdSubcategoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdSubcategoria()
    {
        return $this->hasOne(Subcategoria::class, ['id' => 'subcategoria_id']);
    }

    public function getNombreSubcategoria()
    {
        return $this->hasOne(Subcategoria::class, ['nombre' => 'subcategoria']);
    }

    /**
     * Gets query for [[IdCrossdocking]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['id' => 'crossdocking_id']);
    }

    public function getNombreCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['nombre' => 'crossDocking']);
    }

    public static function getListaData($idagenda, $categoria){
        $data = Agendapresupuestosubcategoria::find()
                        ->alias('aps')
                        ->select(['fechaLlegada AS id', "CONVERT(NVARCHAR(10), fechaLlegada, 120) + ' - ' + FORMAT(SUM(cantidad) - SUM(ISNULL(cantidadAgendada,0)), 'N0') AS nombre"])
                        ->groupBy(['fechaLlegada'])
                        ->andFilterWhere(['>', 'cantidad', 0])
                        ->andWhere(['idAgendaPresupuesto' => $idagenda])
                        ->andWhere(['categoria' => $categoria])
                        ->orderBy('fechaLlegada')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static function totalCantidadCategoria ($idagendapresupuesto, 
                                                    $fechallegada, 
                                                    $crossdocking_id, 
                                                    $categoria_id){

        $total = Agendapresupuestosubcategoria::find()
                                                ->where([
                                                        'idAgendaPresupuesto' => $idagendapresupuesto,
                                                        'fechaLlegada' => $fechallegada,
                                                        'crossdocking_id' => $crossdocking_id,
                                                        'categoria_id' => $categoria_id
                                                ])->sum('cantidad');

        return $total;
    }

    public static function actualizarPresupuesto ($idagendapresupuesto, 
                                                    $fechallegada, 
                                                    $crossdocking_id, 
                                                    $categoria_id, 
                                                    $subcategoria_id){
        
        $total = self::totalCantidadCategoria($idagendapresupuesto, 
                                                $fechallegada, 
                                                $crossdocking_id, 
                                                $categoria_id, 
                                                $subcategoria_id);
    
        $id = Agendapresupuestocategoria::actualizarRegistro($idagendapresupuesto, 
                                                                $fechallegada, 
                                                                $crossdocking_id, 
                                                                $categoria_id, 
                                                                $total);

        if ($id != null){
            $id = Agendapresupuestosemana::actualizarRegistro($idagendapresupuesto, 
                                                                $fechallegada, 
                                                                $crossdocking_id, 
                                                                $categoria_id);
        }

        return true;
    }

}
