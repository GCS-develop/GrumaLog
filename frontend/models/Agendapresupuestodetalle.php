<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "agendapresupuestodetalle".
 *
 * @property int $id
 * @property int $idAgendaPresupuesto
 * @property string|null $codigo
 * @property string|null $subcategoria
 * @property string|null $consumidor
 * @property string|null $universo
 * @property string|null $producto
 * @property string|null $tendencia
 * @property string|null $talla
 * @property string|null $tipo
 * @property string|null $modelo
 * @property float|null $cantidad
 * @property string|null $fechaLlegada
 * @property string|null $crossDocking
 *
 * @property Agendapresupuesto $agendaPresupuesto
 */
class Agendapresupuestodetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendapresupuestodetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idAgendaPresupuesto'], 'required'],
            [['idAgendaPresupuesto', 'inconsistencia', 'fila'], 'integer'],
            [['cantidad'], 'number'],
            [['talla', 'codigo', 'fechaLlegada', 'consumidor', 'universo', ], 'safe'],
            [['subcategoria', 'producto', 'tendencia',  
            'tipo', 'modelo', 'crossDocking'], 'string', 'max' => 50],
            [['idAgendaPresupuesto'], 'exist', 'skipOnError' => true, 'targetClass' => Agendapresupuesto::class, 'targetAttribute' => ['idAgendaPresupuesto' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idAgendaPresupuesto' => 'Id Agenda Presupuesto',
            'codigo' => 'Codigo',
            'subcategoria' => 'Subcategoria',
            'consumidor' => 'Consumidor',
            'universo' => 'Universo',
            'producto' => 'Producto',
            'tendencia' => 'Tendencia',
            'talla' => 'Talla',
            'tipo' => 'Tipo',
            'modelo' => 'Modelo',
            'cantidad' => 'Cantidad',
            'fechaLlegada' => 'Fecha Llegada',
            'crossDocking' => 'Destino',
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
     * Gets query for [[Subcategoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNombreSubcategoria()
    {
        return $this->hasOne(Subcategoria::class, ['nombre' => 'subcategoria']);
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNombreCategoria()
    {
        return $this->hasOne(Categoria::class, ['nombre' => 'categoria']);
    }

    /**
     * Gets query for [[Crossdocking]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNombreCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['nombre' => 'crossDocking']);
    }
}
