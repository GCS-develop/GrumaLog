<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "planillaembarquetraspaso".
 *
 * @property int $id
 * @property int $idPlanillaEmbarque
 * @property int $idTraspaso
 * @property int $idBodegaOrigen
 * @property int $idBodegaDestino
 * @property int $unidades
 * @property int $unidadesEmp
 * @property float $sello
 * @property string $fechaRecibido
 * @property int $idUsuarioRecibido
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $idBodegaDestino0
 * @property Bodegas $idBodegaOrigen0
 * @property Planillaembarque $idPlanillaEmbarque0
 * @property Traspaso $idTraspaso0
 */
class Planillaembarquetraspaso extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planillaembarquetraspaso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'sello', 'fechaRecibido', 'idUsuarioRecibido', 'idEstado', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'idUsuarioRecibido', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['sello'], 'number'],
            [['fechaRecibido', 'created_at', 'updated_at'], 'safe'],
            [['idPlanillaEmbarque'], 'exist', 'skipOnError' => true, 'targetClass' => Planillaembarque::class, 'targetAttribute' => ['idPlanillaEmbarque' => 'id']],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            [['idBodegaOrigen'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaOrigen' => 'id']],
            [['idBodegaDestino'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodegaDestino' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idPlanillaEmbarque' => 'Id Planilla Embarque',
            'idTraspaso' => 'Id Traspaso',
            'idBodegaOrigen' => 'Id Bodega Origen',
            'idBodegaDestino' => 'Id Bodega Destino',
            'unidades' => 'Unidades',
            'unidadesEmp' => 'Unidades Emp',
            'sello' => 'Sello',
            'fechaRecibido' => 'Fecha Recibido',
            'idUsuarioRecibido' => 'Id Usuario Recibido',
            'idEstado' => 'Id Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdBodegaDestino0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdBodegaDestino0()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaDestino']);
    }

    /**
     * Gets query for [[IdBodegaOrigen0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdBodegaOrigen0()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodegaOrigen']);
    }

    /**
     * Gets query for [[IdPlanillaEmbarque0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdPlanillaEmbarque0()
    {
        return $this->hasOne(Planillaembarque::class, ['id' => 'idPlanillaEmbarque']);
    }

    /**
     * Gets query for [[IdTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTraspaso0()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }
}
