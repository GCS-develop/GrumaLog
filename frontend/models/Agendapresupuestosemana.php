<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "agendapresupuestosemana".
 *
 * @property int $id
 * @property int $idAgendaPresupuesto
 * @property int|null $periodoAnio
 * @property int|null $periodoMes
 * @property string|null $crossDocking
 * @property string|null $categoria
 * @property int|null $numeroSemanaAnio
 * @property float|null $cantidad
 */
class Agendapresupuestosemana extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendapresupuestosemana';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idAgendaPresupuesto'], 'required'],
            [['idAgendaPresupuesto', 'periodoAnio', 'periodoMes', 'numeroSemanaAnio'], 'integer'],
            [['cantidad', 'cantidadAgendada'], 'number'],
            [['crossDocking', 'categoria'], 'string', 'max' => 50],
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
            'periodoAnio' => 'Periodo Anio',
            'periodoMes' => 'Periodo Mes',
            'crossDocking' => 'Destino',
            'categoria' => 'Categoria',
            'numeroSemanaAnio' => 'Numero Semana Anio',
            'cantidad' => 'Cantidad',
        ];
    }

    public function getIdCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    public function getIdCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['id' => 'crossdocking_id']);
    }

    public function getNombreCategoria()
    {
        return $this->hasOne(Categoria::class, ['nombre' => 'categoria']);
    }

    public function getNombreCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['nombre' => 'crossDocking']);
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

    public static function totalCantidadSemana ($idagendapresupuesto, 
                                                    $crossdocking_id, 
                                                    $categoria_id,
                                                    $numeroSemanaAnio){

        $total = Agendapresupuestocategoria::find()
                                            ->select(['SUM(cantidad) AS total'])
                                            ->where([
                                                    'idAgendaPresupuesto' => $idagendapresupuesto,
                                                    'crossdocking_id' => $crossdocking_id,
                                                    'categoria_id' => $categoria_id,
                                                    'DATEPART(WEEK, fechaLlegada)' => $numeroSemanaAnio
                                            ])->scalar();                                            
        return $total;
    }

    public static function actualizarRegistro ($idagendapresupuesto, 
                                                $fechallegada, 
                                                $crossdocking_id, 
                                                $categoria_id){

        $id = null;
        $numSemana = date('W', strtotime($fechallegada));

        $total = self::totalCantidadSemana (    $idagendapresupuesto, 
                                                $crossdocking_id, 
                                                $categoria_id,
                                                $numSemana
                                            );

        $model = Agendapresupuestosemana::find()
                                            ->where([
                                                        'idAgendaPresupuesto' => $idagendapresupuesto,
                                                        'crossDocking_id' => $crossdocking_id,
                                                        'categoria_id' => $categoria_id,
                                                        'numeroSemanaAnio' => $numSemana,
                                                    ]
                                            )->one();

        if ($model == null){
            $model = new Agendapresupuestosemana ();
            $model->idAgendaPresupuesto = $idagendapresupuesto;
            $model->crossdocking_id = $crossdocking_id;
            $model->categoria_id = $categoria_id;
            $model->numeroSemanaAnio = $numSemana;

            $model->crossDocking = $model->idCrossdocking->nombre;
            $model->categoria = $model->idCategoria->nombre;
            $model->periodoAnio = $model->agendaPresupuesto->periodoAnio;
            $model->periodoMes = $model->agendaPresupuesto->periodoMes;        
        }

        $model->cantidad = $total;

        if ($model->save()){
            $id = $model->id;
        }

        return $id;
    }

}
