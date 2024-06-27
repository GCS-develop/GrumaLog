<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspasodetalle".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Traspaso $traspaso
 * @property Item $item
 */
class Traspasodetalle extends \yii\db\ActiveRecord
{
    public $bodegaorigen;
    public $bodegadestino;
    public $numerocajas;
    public $codigoitem;
    public $count;
    public $ultimo_codigo;
    public $cantidad_paquetes;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalle';
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
            [['idTraspaso', 'idItem'], 'required'],
            [['idTraspaso', 'idItem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            [
                ['idItem'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Item::class,
                'targetAttribute' => ['idItem' => 'id'],
                'when' => function ($model, $attribute) {
                    // Obtener el valor del campo idItem
                    $idItem = $model->$attribute;

                    // Verificar si el Item asociado está activo
                    $item = Item::findOne(['id' => $idItem, 'idEstado' => 'ACTIVO']);

                    return $item !== null;
                },
                'message' => 'El Item seleccionado no está activo.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTraspaso' => 'Id Traspaso',
            'idItem' => 'Id Item',
            'cantidad' => 'Cantidad',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Traspaso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }

    public function getItem()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idItem'])
                    ->where(['idEstado' => 'ACTIVO']);
    }

    public function getFindCount($idTraspaso)
    {
        return $this->find()->where(['idTraspaso' => $idTraspaso])->count();
    }
    
    public function getCantidadPaquetes()
    {
        return $this->getItems()->where(['unidadOrden' => null])->count();
    }

    public static function getInventario ($codigobarras, $codigobodega = null){

        $existencia = 0;
        $existenciaBodega = 0;

        if ($codigobarras){

            $modelinventario = new InventariosWs();
            $lista = $modelinventario->getAllInventariosSiesa($codigobarras);
            foreach($lista as $bodega){

                $existencia = $existencia + $bodega['CantidadExistente'];

                if ($codigobodega != null){
                    if ($bodega['Bodega'] == $codigobodega) {
                        $existenciaBodega = $existenciaBodega + $bodega['CantidadExistente'];
                    }
                }
            }
        }

        if ($codigobodega != null){
            $existencia = $existenciaBodega;
        }

        return $existencia;
    }
}
