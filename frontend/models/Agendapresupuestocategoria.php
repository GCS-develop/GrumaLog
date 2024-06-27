<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "agendapresupuestocategoria".
 *
 * @property int $id
 * @property int|null $periodoAnio
 * @property int|null $periodoMes
 * @property string|null $crossDocking
 * @property string|null $categoria
 * @property string|null $fechaLlegada
 * @property float|null $cantidad
 */
class Agendapresupuestocategoria extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendapresupuestocategoria';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['periodoAnio', 'periodoMes', 'idAgendaPresupuesto', 'crossdocking_id', 'categoria_id'], 'integer'],
            [['fechaLlegada'], 'safe'],
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
            'periodoAnio' => 'Año',
            'periodoMes' => 'Mes',
            'crossDocking' => 'Destino',
            'categoria' => 'Categoría',
            'fechaLlegada' => 'Fecha Llegada',
            'cantidad' => 'Cantidad',
        ];
    }

    public function getIdCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    public function getNombreCategoria()
    {
        return $this->hasOne(Categoria::class, ['nombre' => 'categoria']);
    }

    public function getIdCrossdocking()
    {
        return $this->hasOne(Crossdocking::class, ['id' => 'crossdocking_id']);
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

    public static function actualizarRegistro ($idagendapresupuesto, 
                                                $fechallegada, 
                                                $crossdocking_id, 
                                                $categoria_id, 
                                                $total){

        $id = null;
        $model = Agendapresupuestocategoria::find()
                                            ->where([
                                                        'idAgendaPresupuesto' => $idagendapresupuesto,
                                                        'fechaLlegada' => $fechallegada,
                                                        'crossDocking_id' => $crossdocking_id,
                                                        'categoria_id' => $categoria_id,
                                                    ]
                                            )->one();

        if ($model == null){
            $model = new Agendapresupuestocategoria ();
            $model->idAgendaPresupuesto = $idagendapresupuesto;
            $model->fechaLlegada = $fechallegada;
            $model->crossdocking_id = $crossdocking_id;
            $model->categoria_id = $categoria_id;

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
