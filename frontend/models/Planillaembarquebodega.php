<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "planillaembarquebodega".
 *
 * @property int $id
 * @property int $idPlanillaEmbarque
 * @property int $idBodegaDestino
 * @property float|null $selloSalida
 * @property float|null $selloLlegada
 * @property int|null $numeroRegistros
 * @property int|null $orden
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $bodegadestino
 * @property Planillaembarque $planillaembarque
 */
class Planillaembarquebodega extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planillaembarquebodega';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idPlanillaEmbarque', 'idBodegaDestino'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idPlanillaEmbarque', 'idBodegaDestino', 'numeroRegistros', 'orden', 'created_by', 'updated_by'], 'integer'],
            [['selloSalida', 'selloLlegada'],  'match', 'pattern' => '/^[A-Za-z0-9]+$/', 'message' => 'El valor solo debe contener letras y números.'],
            [['created_at', 'updated_at'], 'safe'],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
            [['idPlanillaEmbarque'], 'exist', 'skipOnError' => true, 'targetClass' => Planillaembarque::class, 'targetAttribute' => ['idPlanillaEmbarque' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idPlanillaEmbarque' => 'No. Planilla',
            'idBodegaDestino' => 'Bodega Destino',
            'selloSalida' => 'Sello Salida',
            'selloLegada' => 'Sello Llegada',
            'numeroRegistros' => 'No. Registros',
            'orden' => 'Orden',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Bodegadestino]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodegadestino()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaDestino']);
    }

    /**
     * Gets query for [[Planillaembarque]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanillaembarque()
    {
        return $this->hasOne(Planillaembarque::class, ['id' => 'idPlanillaEmbarque']);
    }

    public static function validarSello($model, $sellollegada){
        $selloOK = false;

        $selloValida = $model->ultimoSello;
        if ($model->ultimoSello == null){
            $selloValida = $model->sello;
        }

        if ($selloValida == $sellollegada){
            $selloOK = true;
        }

        return $selloOK;
    }
}
